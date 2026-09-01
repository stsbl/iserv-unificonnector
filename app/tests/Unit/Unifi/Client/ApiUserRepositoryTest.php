<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Unifi\Client;

use IServ\UnifiConnector\Unifi\Client\ApiUserRepository;
use IServ\UnifiConnector\Unifi\Client\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use UniFi_API\Client;

#[CoversClass(ApiUserRepository::class)]
final class ApiUserRepositoryTest extends TestCase
{
    public function testFindAllConvertsLegacyApiUsersAndHandlesFailedRequests(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::exactly(2))->method('list_users')->willReturnOnConsecutiveCalls(
            [['_id' => 'client-id', 'mac' => 'aa:bb:cc:dd:ee:ff', 'name' => 'Printer', 'groupId' => '']],
            false,
        );
        $repository = new ApiUserRepository($client);

        $users = iterator_to_array($repository->findAll());

        self::assertCount(1, $users);
        self::assertNull($users[0]->getGroupId());
        self::assertSame([], iterator_to_array($repository->findAll()));
    }

    public function testSavesExistingAndNewLegacyApiUsers(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('edit_client_name')->with('client-id', 'Printer');
        $client->expects(self::once())->method('create_user')->with('aa:bb:cc:dd:ee:ff', '', 'New printer');
        $repository = new ApiUserRepository($client);

        $repository->save(new User('client-id', 'Printer', 'aa:bb:cc:dd:ee:ff'));
        $repository->save(new User(null, 'New printer', 'aa:bb:cc:dd:ee:ff'));
    }
}
