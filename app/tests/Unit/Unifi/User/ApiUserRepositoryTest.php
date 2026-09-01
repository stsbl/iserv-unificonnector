<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Unifi\User;

use IServ\UnifiConnector\Unifi\User\ApiUserRepository;
use IServ\UnifiConnector\Unifi\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use UniFi_API\Client;

#[CoversClass(ApiUserRepository::class)]
final class ApiUserRepositoryTest extends TestCase
{
    public function testFindAllConvertsApiObjectsAndHandlesFailedRequests(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::exactly(2))->method('list_users')->willReturnOnConsecutiveCalls(
            [(object) ['_id' => 'client-id', 'mac' => 'aa:bb:cc:dd:ee:ff', 'name' => 'Printer', 'usergroup_id' => 'group-id']],
            false,
        );
        $repository = new ApiUserRepository($client);

        $users = iterator_to_array($repository->findAll());

        self::assertCount(1, $users);
        self::assertSame('client-id', $users[0]->getId());
        self::assertSame([], iterator_to_array($repository->findAll()));
    }

    public function testSavesExistingAndNewUsers(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('edit_client_name')->with('client-id', 'Printer');
        $client->expects(self::once())->method('set_usergroup')->with('client-id', 'group-id');
        $client->expects(self::once())->method('create_user')->with('aa:bb:cc:dd:ee:ff', '', null);
        $repository = new ApiUserRepository($client);

        $repository->save(new User('client-id', 'Printer', 'aa:bb:cc:dd:ee:ff', 'group-id'));
        $repository->save(new User(null, null, 'aa:bb:cc:dd:ee:ff'));
    }
}
