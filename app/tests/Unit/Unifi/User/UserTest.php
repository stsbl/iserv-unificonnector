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
}
