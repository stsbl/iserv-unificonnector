<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Integration\Synchronisation;

use IServ\Bundle\IdmDataBroker\Contract\IdmUserGroupMembershipProvider;
use IServ\Bundle\IdmDataBroker\Key\CacheKeyBuilder;
use IServ\Bundle\IdmDataBroker\Service\IdmCacheFetcher;
use IServ\Bundle\IdmDataBroker\Service\UserGroupMembershipFetcher;
use IServ\Bundle\IdmDataBroker\Service\UserRolesFetcher;
use IServ\Bundle\IdmDataBroker\Service\UserRolesProvider;
use IServ\Library\Uuid\Uuid;
use IServ\UnifiConnector\Synchronisation\IdmMembershipFetcher;
use IServ\UnifiConnector\Synchronisation\IdmRoleFetcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

#[CoversClass(IdmMembershipFetcher::class)]
#[CoversClass(IdmRoleFetcher::class)]
final class IdmFetchersTest extends TestCase
{
    public function testFetchesMembershipsThroughTheDataBroker(): void
    {
        $provider = $this->createMock(IdmUserGroupMembershipProvider::class);
        $provider->expects(self::once())->method('fetchUserGroups')->willReturn([
            ['hexUuid' => 'e9b5542e-4f55-46d9-8cd8-b4ff826d55a4'],
        ]);
        $fetcher = new IdmMembershipFetcher(new UserGroupMembershipFetcher($provider, $this->cacheFetcher()));

        $memberships = $fetcher->fetch(Uuid::createFromString('f2b47e1b-a20f-40e0-b9c1-f79782401d07'));

        self::assertSame(['e9b5542e-4f55-46d9-8cd8-b4ff826d55a4'], array_map(static fn(Uuid $uuid): string => $uuid->toNormalizedString(), $memberships->groupUuids));
    }

    public function testFetchesRolesThroughTheDataBroker(): void
    {
        $provider = $this->createMock(UserRolesProvider::class);
        $provider->expects(self::once())->method('fetchUserRoles')->willReturn([
            'ROLE_TEACHER' => 'c5ac939a-2d74-4630-af64-7093f0cbd251',
        ]);
        $fetcher = new IdmRoleFetcher(new UserRolesFetcher($provider, $this->cacheFetcher()));

        $roles = $fetcher->getUserRoles(Uuid::createFromString('f2b47e1b-a20f-40e0-b9c1-f79782401d07'));

        self::assertSame(['ROLE_TEACHER' => 'c5ac939a-2d74-4630-af64-7093f0cbd251'], $roles->roles);
    }

    private function cacheFetcher(): IdmCacheFetcher
    {
        return new IdmCacheFetcher(new TagAwareAdapter(new ArrayAdapter()), new CacheKeyBuilder('unificonnector', 'test'));
    }
}
