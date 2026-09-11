<?php

declare(strict_types=1);

namespace Stsbl\IServ\UnifiConnector\Infrastructure\Idm;

interface AutocompleteRoleProviderInterface
{
    /** @return list<AutocompleteUser> */
    public function searchUsers(string $query): array;

    /** @return list<AutocompleteGroup> */
    public function searchGroups(string $query): array;

    /** @return list<AutocompleteRole> */
    public function search(string $query): array;

    public function get(string $uuid): ?AutocompleteRole;
}
