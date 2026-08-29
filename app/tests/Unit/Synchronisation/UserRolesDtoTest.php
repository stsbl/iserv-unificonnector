<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Synchronisation;

use IServ\Bundle\IdmDataBroker\Dto\UserRolesDto;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class UserRolesDtoTest extends TestCase
{
    public function testRoleUuidsAreDtoValuesRatherThanLegacyRoleNames(): void
    {
        $roles = new UserRolesDto([
            'ROLE_SYSTEMADMINISTRATOR' => 'f2b47e1b-a20f-40e0-b9c1-f79782401d07',
        ]);

        self::assertSame(['f2b47e1b-a20f-40e0-b9c1-f79782401d07'], array_values($roles->roles));
    }
}
