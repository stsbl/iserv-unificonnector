<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Mapping;

interface MappingResolver
{
    /**
     * @param list<string> $groupUuids
     * @param list<string> $roleUuids
     */
    public function groupForMemberships(?string $userUuid, array $groupUuids, array $roleUuids): ?string;
}
