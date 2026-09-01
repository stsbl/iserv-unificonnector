<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Infrastructure\Idm;

interface AutocompleteRoleProviderInterface
{
    /** @return list<AutocompleteRole> */
    public function search(string $query): array;

    public function get(string $uuid): ?AutocompleteRole;
}
