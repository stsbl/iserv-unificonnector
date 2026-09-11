<?php

declare(strict_types=1);

namespace Stsbl\IServ\UnifiConnector\Infrastructure\Idm;

use IServ\Library\IdmApiClient\Hydrator\CallbackHydrator;
use IServ\Library\IdmApiClient\IdmClientInterface;

/** Queries IDM's unrestricted autocomplete endpoints and human-readable role names. */
final readonly class AutocompleteRoleProvider implements AutocompleteRoleProviderInterface
{
    private const AUTOCOMPLETE_INCLUDE_PRIVILEGE = '464e390f-5cea-4835-b40c-c3d3303ae234';
    private const AUTOCOMPLETE_INCLUDE_GROUP_FLAG = '62301b70-1b9b-43da-b56d-1d717d171e16';

    /** @psalm-suppress PossiblyUnusedMethod Constructed through Symfony autowiring. */
    public function __construct(private IdmClientInterface $client)
    {
    }

    /** @return list<AutocompleteUser> */
    public function searchUsers(string $query): array
    {
        return $this->client->performRequest(
            'GET',
            'iserv/idm/api/v1/lookup/users?' . http_build_query([
                'query' => $query,
                'type' => 'user',
                'includePrivilege' => self::AUTOCOMPLETE_INCLUDE_PRIVILEGE,
                '_attributes' => 'hexUuid,user,firstname,lastname,auxInfo',
            ], encoding_type: PHP_QUERY_RFC3986),
            new CallbackHydrator(fn(array $data): array => $this->lookupMatches($data, AutocompleteUser::fromApiResponse(...))),
        );
    }

    /** @return list<AutocompleteGroup> */
    public function searchGroups(string $query): array
    {
        return $this->client->performRequest(
            'GET',
            'iserv/idm/api/v1/lookup/groups?' . http_build_query([
                'query' => $query,
                'includeFlag' => self::AUTOCOMPLETE_INCLUDE_GROUP_FLAG,
                '_attributes' => 'hexUuid,group,name',
            ], encoding_type: PHP_QUERY_RFC3986),
            new CallbackHydrator(fn(array $data): array => $this->lookupMatches($data, AutocompleteGroup::fromApiResponse(...))),
        );
    }

    /** @return list<AutocompleteRole> */
    public function search(string $query): array
    {
        return $this->client->performRequest(
            'GET',
            'iserv/idm/api/v1/roles?' . http_build_query([
                'name[icontains]' => $query,
                '_attributes' => 'hexUuid,name,module',
            ], encoding_type: PHP_QUERY_RFC3986),
            new CallbackHydrator(static fn(array $data): array => array_values(array_filter(array_map(
                static fn(mixed $role): ?AutocompleteRole => is_array($role) ? AutocompleteRole::fromApiResponse($role) : null,
                $data,
            )))),
        );
    }

    public function get(string $uuid): ?AutocompleteRole
    {
        return $this->client->performRequest(
            'GET',
            'iserv/idm/api/v1/roles/' . rawurlencode($uuid) . '?_attributes=hexUuid,name,module',
            new CallbackHydrator(static fn(array $data): ?AutocompleteRole => AutocompleteRole::fromApiResponse($data)),
        );
    }

    /**
     * @template T of object
     * @param array<array-key, mixed> $data
     * @param callable(array<array-key, mixed>): ?T $factory
     * @return list<T>
     */
    private function lookupMatches(array $data, callable $factory): array
    {
        $matches = [];
        foreach (['exact', 'partial', 'fuzzy'] as $type) {
            $rawItems = $data[$type] ?? [];
            if (!is_array($rawItems)) {
                continue;
            }
            foreach (array_filter($rawItems, 'is_array') as $item) {
                if (null !== $match = $factory($item)) {
                    $matches[] = $match;
                }
            }
        }

        return $matches;
    }
}
