<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Infrastructure\Idm;

use IServ\Library\IdmApiClient\Hydrator\CallbackHydrator;
use IServ\Library\IdmApiClient\IdmClientInterface;

/** Queries IDM roles live: the DataBroker only provides cached roles for a specific user. */
final readonly class AutocompleteRoleProvider
{
    /** @psalm-suppress PossiblyUnusedMethod Constructed through Symfony autowiring. */
    public function __construct(private IdmClientInterface $client)
    {
    }

    /** @return list<AutocompleteRole> */
    public function search(string $query): array
    {
        $roles = $this->client->performRequest(
            'GET',
            'iserv/idm/api/v1/roles?' . http_build_query([
                'role[icontains]' => $query,
                '_attributes' => 'hexUuid,role,name,module',
            ]),
            new CallbackHydrator(static fn(array $data): array => array_values(array_filter(array_map(
                static fn(mixed $role): ?AutocompleteRole => is_array($role) ? AutocompleteRole::fromApiResponse($role) : null,
                $data,
            )))),
        );

        return $roles;
    }

    public function get(string $uuid): ?AutocompleteRole
    {
        $role = $this->client->performRequest(
            'GET',
            'iserv/idm/api/v1/roles/' . rawurlencode($uuid) . '?_attributes=hexUuid,role,name,module',
            new CallbackHydrator(static fn(array $data): ?AutocompleteRole => AutocompleteRole::fromApiResponse($data)),
        );

        return $role;
    }
}
