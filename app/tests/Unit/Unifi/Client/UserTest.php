<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Unifi\Client;

use IServ\UnifiConnector\Unifi\Client\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
final class UserTest extends TestCase
{
    public function testUpdatesAndComparesClient(): void
    {
        $user = User::fromApiResponse(['_id' => 'id', 'name' => 'Old', 'mac' => 'AA:BB:CC:DD:EE:FF', 'groupId' => '']);
        $updated = new User('id', 'New', 'aa:bb:cc:dd:ee:ff', 'group');
        $user->updateFrom($updated);
        $user->setMac('aa:bb:cc:dd:ee:ff');
        $user->setGroupId('staff');

        self::assertSame('id', $user->getId());
        self::assertSame('New', $user->getName());
        self::assertSame('staff', $user->getGroupId());
        $user->setGroupId('group');
        self::assertTrue($user->equals($updated));
    }

    public function testKeepsDistinctNamesAndGroupsUnequal(): void
    {
        $user = new User(null, 'Printer', 'aa:bb:cc:dd:ee:ff', 'staff');

        self::assertFalse($user->equals(new User(null, 'Other printer', 'aa:bb:cc:dd:ee:ff', 'staff')));
        self::assertFalse($user->equals(new User(null, 'Printer', 'ff:ee:dd:cc:bb:aa', 'staff')));
        self::assertFalse($user->equals(new User(null, 'Printer', 'aa:bb:cc:dd:ee:ff', 'students')));
    }
}
