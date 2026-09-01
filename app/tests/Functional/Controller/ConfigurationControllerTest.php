<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Functional\Controller;

use IServ\UnifiConnector\Controller\ConfigurationController;
use IServ\UnifiConnector\Configuration\FileConfigurationRepository;
use IServ\UnifiConnector\Entity\UniFiGroupMapping;
use IServ\UnifiConnector\Infrastructure\Form\MappingSettingsType;
use IServ\UnifiConnector\Unifi\UserGroup\UserGroup;
use IServ\UnifiConnector\Unifi\UserGroup\UserGroupRepository;
use IServ\UnifiConnector\Security\Privileges;
use IServ\Bundle\TestBrowser\Test\TestBrowser;
use IServ\Library\UserToken\Test\User\TestUserBuilder;
use IServ\Library\Uuid\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(ConfigurationController::class)]
#[CoversClass(MappingSettingsType::class)]
final class ConfigurationControllerTest extends WebTestCase
{
    private string $configurationPath;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $manager = self::getContainer()->get(EntityManagerInterface::class);
        $schema = new SchemaTool($manager);
        $schema->dropSchema($manager->getMetadataFactory()->getAllMetadata());
        $schema->createSchema($manager->getMetadataFactory()->getAllMetadata());
        self::ensureKernelShutdown();
        $this->configurationPath = tempnam(sys_get_temp_dir(), 'unificonnector-config-') ?: throw new \RuntimeException('Could not create temporary file.');
        unlink($this->configurationPath);
    }

    protected function tearDown(): void
    {
        @unlink($this->configurationPath);
    }

    public function testConfigurationRequiresModulePrivilege(): void
    {
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->request('GET', '/admin/unificonnector/');

        self::assertResponseStatusCodeSame(403);
    }

    public function testConfigurationAllowsAdminWithModulePrivilege(): void
    {
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->loginAdmin(TestUserBuilder::create(Uuid::createFromString('f2b47e1b-a20f-40e0-b9c1-f79782401d07'))
            ->privilege(Privileges::ADMIN)
            ->getUser());
        $client->request('GET', '/admin/unificonnector/');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Connection', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('Start synchronization', (string) $client->getResponse()->getContent());
        $scripts = urldecode(implode("\n", $client->getResponse()->headers->all('X-IServ-Response-Script')));
        self::assertStringContainsString('/iserv/js/static/', $scripts);
        self::assertStringNotContainsString('/iserv/unificonnector/js/polyfill.min.js', $scripts);
        self::assertStringNotContainsString('/iserv/js/static/js/polyfill.min.js', $scripts);
        self::assertStringNotContainsString('/iserv/unificonnector/js/lang/de.js', $scripts);
        self::assertCount(1, $client->getCrawler()->filterXpath('//form[@action="/admin/unificonnector/sync"]'));
        self::assertGreaterThanOrEqual(1, $client->getCrawler()->filterXpath('//form[@action="/admin/unificonnector/"]')->count());
    }

    public function testAuthenticatedAdminStoresApiKeyConfiguration(): void
    {
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->disableReboot();
        self::getContainer()->set(FileConfigurationRepository::class, new FileConfigurationRepository($this->configurationPath));
        $client->loginAdmin(TestUserBuilder::create(Uuid::createFromString('f2b47e1b-a20f-40e0-b9c1-f79782401d07'))
            ->privilege(Privileges::ADMIN)
            ->getUser());
        $client->request('POST', '/admin/unificonnector/', [
            'connection_settings' => [
                'url' => 'unifi.example.test',
                'authenticationMode' => 'api_key',
                'username' => '',
                'password' => '',
                'apiKey' => 'secret',
                'fallbackGroup' => '',
            ],
        ]);

        self::assertResponseRedirects('/admin/unificonnector/');
        self::assertSame('secret', (new FileConfigurationRepository($this->configurationPath))->find()?->apiKey);
    }

    public function testAuthenticatedAdminSeesErrorForMissingSelectedCredentials(): void
    {
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->loginAdmin(TestUserBuilder::create(Uuid::createFromString('f2b47e1b-a20f-40e0-b9c1-f79782401d07'))
            ->privilege(Privileges::ADMIN)
            ->getUser());
        $client->request('POST', '/admin/unificonnector/', [
            'connection_settings' => [
                'url' => 'unifi.example.test',
                'authenticationMode' => 'api_key',
                'username' => '',
                'password' => '',
                'apiKey' => '',
                'fallbackGroup' => '',
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('connection_settings_apiKey', (string) $client->getResponse()->getContent());
    }

    public function testAuthenticatedAdminDeletesStoredApiKey(): void
    {
        $repository = new FileConfigurationRepository($this->configurationPath);
        $repository->store(new \IServ\UnifiConnector\Configuration\ConnectionConfiguration('https://unifi.example.test', '', '', '', 'api_key', 'secret'));
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->disableReboot();
        self::getContainer()->set(FileConfigurationRepository::class, $repository);
        $client->loginAdmin(TestUserBuilder::create(Uuid::createFromString('f2b47e1b-a20f-40e0-b9c1-f79782401d07'))
            ->privilege(Privileges::ADMIN)
            ->getUser());
        $client->request('POST', '/admin/unificonnector/', ['unificonnector_delete_api_key' => ['submit' => '']]);

        self::assertResponseRedirects('/admin/unificonnector/');
        self::assertSame('', $repository->find()?->apiKey);
        self::assertSame('password', $repository->find()?->authenticationMode);
    }

    public function testAuthenticatedAdminRetainsStoredApiKeyWhenItIsNotResubmitted(): void
    {
        $repository = new FileConfigurationRepository($this->configurationPath);
        $repository->store(new \IServ\UnifiConnector\Configuration\ConnectionConfiguration('https://unifi.example.test', '', '', '', 'api_key', 'secret'));
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->disableReboot();
        self::getContainer()->set(FileConfigurationRepository::class, $repository);
        $client->loginAdmin(TestUserBuilder::create(Uuid::createFromString('f2b47e1b-a20f-40e0-b9c1-f79782401d07'))
            ->privilege(Privileges::ADMIN)
            ->getUser());
        $client->request('POST', '/admin/unificonnector/', [
            'connection_settings' => [
                'url' => 'unifi.example.test',
                'authenticationMode' => 'api_key',
                'username' => '',
                'password' => '',
                'apiKey' => '',
                'fallbackGroup' => '',
            ],
        ]);

        self::assertResponseRedirects('/admin/unificonnector/');
        self::assertSame('secret', $repository->find()?->apiKey);
    }

    public function testAuthenticatedAdminSeesStoredConnectionSettings(): void
    {
        $repository = new FileConfigurationRepository($this->configurationPath);
        $repository->store(new \IServ\UnifiConnector\Configuration\ConnectionConfiguration('https://unifi.example.test', 'admin', 'password', 'Fallback', 'password'));
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->disableReboot();
        self::getContainer()->set(FileConfigurationRepository::class, $repository);
        $client->loginAdmin(TestUserBuilder::create(Uuid::createFromString('f2b47e1b-a20f-40e0-b9c1-f79782401d07'))
            ->privilege(Privileges::ADMIN)
            ->getUser());
        $client->request('GET', '/admin/unificonnector/');

        self::assertResponseIsSuccessful();
        self::assertSame('https://unifi.example.test', $client->getCrawler()->filterXpath('//*[@id="connection_settings_url"]')->attr('value'));
        self::assertSame('admin', $client->getCrawler()->filterXpath('//*[@id="connection_settings_username"]')->attr('value'));
    }

    public function testAuthenticatedAdminCreatesMapping(): void
    {
        $groups = $this->createMock(UserGroupRepository::class);
        $groups->method('all')->willReturn([new UserGroup('group-id', 'site', 'Staff')]);
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->disableReboot();
        self::getContainer()->set(UserGroupRepository::class, $groups);
        $client->loginAdmin(TestUserBuilder::create(Uuid::createFromString('f2b47e1b-a20f-40e0-b9c1-f79782401d07'))
            ->privilege(Privileges::ADMIN)
            ->getUser());
        $client->request('POST', '/admin/unificonnector/', [
            'mapping_settings' => ['id' => 'Staff', 'name' => 'Staff members', 'priority' => 1, 'subjects' => []],
        ]);

        self::assertResponseRedirects('/admin/unificonnector/');
        $mapping = self::getContainer()->get(EntityManagerInterface::class)->find(UniFiGroupMapping::class, 'Staff');
        self::assertInstanceOf(UniFiGroupMapping::class, $mapping);
        self::assertSame('Staff members', $mapping->name());
    }

    public function testAuthenticatedAdminDeletesMapping(): void
    {
        self::bootKernel();
        $manager = self::getContainer()->get(EntityManagerInterface::class);
        $mapping = new UniFiGroupMapping('Staff', 'Staff members', 1);
        $manager->persist($mapping);
        $manager->flush();
        self::ensureKernelShutdown();
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->loginAdmin(TestUserBuilder::create(Uuid::createFromString('f2b47e1b-a20f-40e0-b9c1-f79782401d07'))
            ->privilege(Privileges::ADMIN)
            ->getUser());
        $client->request('POST', '/admin/unificonnector/', ['delete_Staff' => ['id' => 'Staff', 'submit' => '']]);

        self::assertResponseRedirects('/admin/unificonnector/');
        self::assertNull(self::getContainer()->get(EntityManagerInterface::class)->find(UniFiGroupMapping::class, 'Staff'));
    }

    public function testAuthenticatedAdminMovesMapping(): void
    {
        self::bootKernel();
        $manager = self::getContainer()->get(EntityManagerInterface::class);
        $first = new UniFiGroupMapping('First', 'First', 1);
        $second = new UniFiGroupMapping('Second', 'Second', 2);
        $manager->persist($first);
        $manager->persist($second);
        $manager->flush();
        self::ensureKernelShutdown();
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->loginAdmin(TestUserBuilder::create(Uuid::createFromString('f2b47e1b-a20f-40e0-b9c1-f79782401d07'))
            ->privilege(Privileges::ADMIN)
            ->getUser());
        $client->request('POST', '/admin/unificonnector/', ['move_down_First' => ['id' => 'First', 'direction' => 'down', 'submit' => '']]);

        self::assertResponseRedirects('/admin/unificonnector/');
        self::assertSame(2, self::getContainer()->get(EntityManagerInterface::class)->find(UniFiGroupMapping::class, 'First')?->priority());
        self::assertSame(1, self::getContainer()->get(EntityManagerInterface::class)->find(UniFiGroupMapping::class, 'Second')?->priority());
    }

}
