<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Functional\Controller;

use IServ\UnifiConnector\Controller\ConfigurationController;
use IServ\UnifiConnector\Security\Privileges;
use IServ\Bundle\TestBrowser\Test\TestBrowser;
use IServ\Library\UserToken\Test\User\TestUserBuilder;
use IServ\Library\Uuid\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(ConfigurationController::class)]
final class ConfigurationControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $manager = self::getContainer()->get(EntityManagerInterface::class);
        $schema = new SchemaTool($manager);
        $schema->dropSchema($manager->getMetadataFactory()->getAllMetadata());
        $schema->createSchema($manager->getMetadataFactory()->getAllMetadata());
        self::ensureKernelShutdown();
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
    }
}
