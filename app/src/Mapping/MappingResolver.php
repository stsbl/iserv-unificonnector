<?php

declare(strict_types=1);

namespace Stsbl\IServ\UnifiConnector\Mapping;

interface MappingResolver
{
    /**
     * @param list<string> $groupUuids
     * @param list<string> $roleUuids
     */
    public function groupForMemberships(?string $userUuid, array $groupUuids, array $roleUuids): ?string;
}
