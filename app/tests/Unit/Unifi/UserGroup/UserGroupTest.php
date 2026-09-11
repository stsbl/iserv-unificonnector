<?php

declare(strict_types=1);

namespace Stsbl\IServ\UnifiConnector\Tests\Unit\Unifi\UserGroup;

use Stsbl\IServ\UnifiConnector\Unifi\UserGroup\UserGroup;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserGroup::class)]
final class UserGroupTest extends TestCase
{
    public function testCreatesUserGroupFromApiObject(): void
    {
        $group = UserGroup::fromApiResponse((object) ['_id' => 'group-id', 'site_id' => 'site-id', 'name' => 'Teachers']);

        self::assertSame('group-id', $group->getId());
        self::assertSame('site-id', $group->getSiteId());
        self::assertSame('Teachers', $group->getName());
    }
}
