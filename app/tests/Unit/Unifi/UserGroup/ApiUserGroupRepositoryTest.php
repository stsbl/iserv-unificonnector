<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Unifi\UserGroup;

use IServ\UnifiConnector\Unifi\UserGroup\ApiUserGroupRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use UniFi_API\Client;

#[CoversClass(ApiUserGroupRepository::class)]
final class ApiUserGroupRepositoryTest extends TestCase
{
    public function testListsGroupsAndFindsByName(): void
    {
        $client = $this->createMock(Client::class);
        $groups = [
            (object) ['_id' => 'first', 'site_id' => 'site', 'name' => 'Staff'],
            ['_id' => 'second', 'site_id' => 'site', 'name' => 'Students'],
        ];
        $client->expects(self::exactly(2))->method('list_usergroups')->willReturn($groups);
        $repository = new ApiUserGroupRepository($client);

        self::assertCount(2, iterator_to_array($repository->all()));
        self::assertSame('second', $repository->findByName('Students')?->getId());
    }

    public function testReturnsEmptyResultsWhenTheApiRequestFails(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::exactly(2))->method('list_usergroups')->willReturn(false);
        $repository = new ApiUserGroupRepository($client);

        self::assertSame([], $repository->all());
        self::assertNull($repository->findByName('Students'));
    }

    public function testReturnsNullWhenNoGroupHasTheRequestedName(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('list_usergroups')->willReturn([['_id' => 'staff', 'site_id' => 'site', 'name' => 'Staff']]);

        self::assertNull((new ApiUserGroupRepository($client))->findByName('Students'));
    }
}
