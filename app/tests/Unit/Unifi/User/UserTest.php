<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Unifi\User;

use IServ\UnifiConnector\Unifi\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
final class UserTest extends TestCase
{
    public function testCreatesUserFromApiObject(): void
    {
        $user = User::fromApiResponse((object) ['_id' => 'user-id', 'mac' => 'aa:bb:cc:dd:ee:ff', 'name' => 'John Doe', 'usergroup_id' => 'group-id']);

        self::assertSame('user-id', $user->getId());
        self::assertSame('John Doe', $user->getName());
        self::assertSame('group-id', $user->getGroupId());
    }

    public function testTreatsEmptyApiGroupAsNoGroup(): void
    {
        $user = User::fromApiResponse(['_id' => 'user-id', 'mac' => 'aa:bb:cc:dd:ee:ff', 'usergroup_id' => '']);

        self::assertTrue($user->equals(new User(null, null, 'aa:bb:cc:dd:ee:ff')));
    }

    public function testUpdatesMutableFieldsAndComparesMacAddressesCaseInsensitively(): void
    {
        $user = new User('id', 'Old', 'AA:BB:CC:DD:EE:FF');
        $user->setMac('aa:bb:cc:dd:ee:ff');
        $user->setGroupId('group');
        $user->updateFrom(new User('other', 'New', 'aa:bb:cc:dd:ee:ff', null));

        self::assertSame('New', $user->getName());
        self::assertNull($user->getGroupId());
        self::assertTrue($user->equals(new User(null, 'New', 'AA:BB:CC:DD:EE:FF')));
    }
}
