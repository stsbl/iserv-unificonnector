<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Entity;

use IServ\UnifiConnector\Entity\GroupAssignment;
use IServ\UnifiConnector\Entity\RoleAssignment;
use IServ\UnifiConnector\Entity\UniFiGroupMapping;
use IServ\UnifiConnector\Entity\UserAssignment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UniFiGroupMapping::class)]
#[CoversClass(GroupAssignment::class)]
#[CoversClass(RoleAssignment::class)]
#[CoversClass(UserAssignment::class)]
final class AssignmentsTest extends TestCase
{
    public function testAssignmentAggregateExposesItsIdentityAndRoleAssignment(): void
    {
        $mapping = new UniFiGroupMapping('unifi-group', 'Teachers', 2);
        $group = new GroupAssignment($mapping, 'f2b47e1b-a20f-40e0-b9c1-f79782401d07');
        $role = new RoleAssignment($mapping, 'f2b47e1b-a20f-40e0-b9c1-f79782401d08');
        $user = new UserAssignment($mapping, 'f2b47e1b-a20f-40e0-b9c1-f79782401d09');
        $mapping->addGroupAssignment($group->groupUuid());
        $mapping->addRoleAssignment($role->roleUuid());
        $mapping->addUserAssignment($user->userUuid());
        $mapping->setPriority(1);

        self::assertSame('unifi-group', $mapping->id());
        self::assertSame('Teachers', $mapping->name());
        self::assertSame(1, $mapping->priority());
        self::assertSame($mapping, $group->mapping());
        self::assertSame($mapping, $role->mapping());
        self::assertSame($mapping, $user->mapping());
        self::assertCount(1, $mapping->roleAssignments());
    }
}
