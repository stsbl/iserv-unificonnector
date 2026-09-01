<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Functional\Controller;

use IServ\Bundle\TestBrowser\Test\TestBrowser;
use IServ\Library\UserToken\Test\User\TestUserBuilder;
use IServ\Library\Uuid\Uuid;
use IServ\UnifiConnector\Controller\SyncController;
use IServ\UnifiConnector\Security\Privileges;
use IServ\UnifiConnector\Synchronisation\SyncRunnerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(SyncController::class)]
final class SyncControllerTest extends WebTestCase
{
    public function testSynchronizationRequiresTheModulePrivilege(): void
    {
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->request('POST', '/admin/unificonnector/sync');

        self::assertResponseStatusCodeSame(403);
    }

    public function testSynchronizationRejectsAnInvalidForm(): void
    {
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->loginAdmin(TestUserBuilder::create(Uuid::createFromString('f2b47e1b-a20f-40e0-b9c1-f79782401d07'))
            ->privilege(Privileges::ADMIN)
            ->getUser());
        $client->request('POST', '/admin/unificonnector/sync');

        self::assertResponseStatusCodeSame(403);
    }

    public function testValidFormStreamsMockedRunnerOutputAndSetsStreamingHeaders(): void
    {
        $runner = $this->createMock(SyncRunnerInterface::class);
        $runner->expects(self::once())->method('stream')->willReturnCallback(static function (callable $write): void {
            $write("Synchronization completed.\n");
        });
        /** @var TestBrowser $client */
        $client = self::createClient();
        $client->disableReboot();
        self::getContainer()->set(SyncRunnerInterface::class, $runner);
        $client->loginAdmin(TestUserBuilder::create(Uuid::createFromString('f2b47e1b-a20f-40e0-b9c1-f79782401d07'))
            ->privilege(Privileges::ADMIN)
            ->getUser());

        $client->request('POST', '/admin/unificonnector/sync', [
            'unificonnector_sync' => ['submit' => ''],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSame('text/plain; charset=utf-8', $client->getResponse()->headers->get('Content-Type'));
        self::assertStringContainsString('no-cache, no-transform', (string) $client->getResponse()->headers->get('Cache-Control'));
        self::assertSame('no', $client->getResponse()->headers->get('X-Accel-Buffering'));
    }
}
