<?php

declare(strict_types=1);

namespace Stsbl\IServ\UnifiConnector\Tests\Unit\Infrastructure\Idm;

use IServ\Library\IdmApiClient\Hydrator\CallbackHydrator;
use IServ\Library\IdmApiClient\IdmClientInterface;
use Stsbl\IServ\UnifiConnector\Infrastructure\Idm\AutocompleteRoleProvider;
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
            self::assertStringContainsString('name%5Bicontains%5D=admin', $path);
            self::assertStringNotContainsString('role%5Bicontains%5D', $path);

            return $hydrator->hydrate([
                ['hexUuid' => 'bbdedf7d-d3c8-4715-bfad-1eec89bc927a', 'name' => 'Administration', 'module' => 'core'],
                ['role' => 'invalid'],
            ]);
        });

        $roles = (new AutocompleteRoleProvider($client))->search('admin');

        self::assertCount(1, $roles);
        self::assertSame('Administration', $roles[0]->displayName());
    }

    public function testSearchUsersUsesIdmLookupAndIncludesFuzzyMatches(): void
    {
        $client = $this->createMock(IdmClientInterface::class);
        $client->expects(self::once())->method('performRequest')->willReturnCallback(static function (string $method, string $path, CallbackHydrator $hydrator): array {
            self::assertSame('GET', $method);
            self::assertSame('iserv/idm/api/v1/lookup/users?query=Ada%20Lovelace&type=user&includePrivilege=464e390f-5cea-4835-b40c-c3d3303ae234&_attributes=hexUuid%2Cuser%2Cfirstname%2Clastname%2CauxInfo', $path);

            return $hydrator->hydrate(['exact' => [], 'partial' => [], 'fuzzy' => [['hexUuid' => 'f2b47e1b-a20f-40e0-b9c1-f79782401d07', 'user' => 'ada', 'firstname' => 'Ada', 'lastname' => 'Lovelace']]]);
        });

        $users = (new AutocompleteRoleProvider($client))->searchUsers('Ada Lovelace');

        self::assertSame('Ada Lovelace', $users[0]->displayName());
    }

    public function testSearchGroupsUsesIdmLookupAndIncludesFuzzyMatches(): void
    {
        $client = $this->createMock(IdmClientInterface::class);
        $client->expects(self::once())->method('performRequest')->willReturnCallback(static function (string $method, string $path, CallbackHydrator $hydrator): array {
            self::assertSame('GET', $method);
            self::assertSame('iserv/idm/api/v1/lookup/groups?query=Teachers&includeFlag=62301b70-1b9b-43da-b56d-1d717d171e16&_attributes=hexUuid%2Cgroup%2Cname', $path);

            return $hydrator->hydrate(['exact' => [], 'partial' => [], 'fuzzy' => [['hexUuid' => 'f2b47e1b-a20f-40e0-b9c1-f79782401d07', 'group' => 'teachers', 'name' => 'Teachers']]]);
        });

        $groups = (new AutocompleteRoleProvider($client))->searchGroups('Teachers');

        self::assertSame('Teachers', $groups[0]->displayName());
    }

    public function testGetUsesAUrlEncodedUuidAndHydratesRole(): void
    {
        $client = $this->createMock(IdmClientInterface::class);
        $client->expects(self::once())->method('performRequest')->willReturnCallback(static function (string $method, string $path, CallbackHydrator $hydrator): mixed {
            self::assertSame('GET', $method);
            self::assertStringContainsString('/roles/role%2Fuuid?', $path);

            return $hydrator->hydrate(['hexUuid' => '457b124d-59ea-4973-b584-b0b9452a18c2', 'name' => 'Administration']);
        });

        $role = (new AutocompleteRoleProvider($client))->get('role/uuid');

        self::assertSame('Administration', $role?->displayName());
    }
}
