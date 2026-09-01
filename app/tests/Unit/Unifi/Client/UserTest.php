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

        self::assertSame('id', $user->getId());
        self::assertSame('New', $user->getName());
        self::assertSame('group', $user->getGroupId());
        self::assertTrue($user->equals($updated));
    }
}
