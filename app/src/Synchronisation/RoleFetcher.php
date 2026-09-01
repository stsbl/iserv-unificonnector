<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Synchronisation;

use IServ\Bundle\IdmDataBroker\Dto\UserRolesDto;
use IServ\Library\Uuid\UuidInterface;

interface RoleFetcher
{
    public function getUserRoles(UuidInterface $userUuid): UserRolesDto;
}
