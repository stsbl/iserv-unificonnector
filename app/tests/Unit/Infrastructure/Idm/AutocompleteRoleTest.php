<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Infrastructure\Idm;

use IServ\UnifiConnector\Infrastructure\Idm\AutocompleteRole;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AutocompleteRole::class)]
final class AutocompleteRoleTest extends TestCase
{
    public function testCreatesRoleFromIdmResponse(): void
    {
        $role = AutocompleteRole::fromApiResponse([
            'hexUuid' => 'f2b47e1b-a20f-40e0-b9c1-f79782401d07',
            'role' => 'ROLE_SYSTEMADMINISTRATOR',
            'name' => 'System administration',
            'module' => 'core',
        ]);

        self::assertNotNull($role);
        self::assertSame('System administration', $role->displayName());
    }

    public function testRejectsMissingRoleUuid(): void
    {
        self::assertNull(AutocompleteRole::fromApiResponse(['role' => 'ROLE_SYSTEMADMINISTRATOR']));
    }
}
