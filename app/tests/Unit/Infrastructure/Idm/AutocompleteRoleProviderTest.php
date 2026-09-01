<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Infrastructure\Idm;

use IServ\Library\IdmApiClient\Hydrator\CallbackHydrator;
use IServ\Library\IdmApiClient\IdmClientInterface;
use IServ\UnifiConnector\Infrastructure\Idm\AutocompleteRoleProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AutocompleteRoleProvider::class)]
final class AutocompleteRoleProviderTest extends TestCase
{
    public function testSearchHydratesOnlyValidRoleResponses(): void
    {
        $client = $this->createMock(IdmClientInterface::class);
        $client->expects(self::once())->method('performRequest')->willReturnCallback(static function (string $method, string $path, CallbackHydrator $hydrator): array {
            self::assertSame('GET', $method);
            self::assertStringContainsString('role%5Bicontains%5D=admin', $path);

            return $hydrator->hydrate([
                ['hexUuid' => 'bbdedf7d-d3c8-4715-bfad-1eec89bc927a', 'role' => 'ROLE_ADMIN', 'name' => 'Administration', 'module' => 'core'],
                ['role' => 'invalid'],
            ]);
        });

        $roles = (new AutocompleteRoleProvider($client))->search('admin');

        self::assertCount(1, $roles);
        self::assertSame('Administration', $roles[0]->displayName());
    }

    public function testGetUsesAUrlEncodedUuidAndHydratesRole(): void
    {
        $client = $this->createMock(IdmClientInterface::class);
        $client->expects(self::once())->method('performRequest')->willReturnCallback(static function (string $method, string $path, CallbackHydrator $hydrator): mixed {
            self::assertSame('GET', $method);
            self::assertStringContainsString('/roles/role%2Fuuid?', $path);

            return $hydrator->hydrate(['hexUuid' => '457b124d-59ea-4973-b584-b0b9452a18c2', 'role' => 'ROLE_ADMIN']);
        });

        $role = (new AutocompleteRoleProvider($client))->get('role/uuid');

        self::assertSame('ROLE_ADMIN', $role?->displayName());
    }
}
