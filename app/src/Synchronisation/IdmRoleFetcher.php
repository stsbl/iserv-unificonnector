<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Synchronisation;

use IServ\Bundle\IdmDataBroker\Dto\UserRolesDto;
use IServ\Bundle\IdmDataBroker\Service\UserRolesFetcher;
use IServ\Library\Uuid\UuidInterface;

final readonly class IdmRoleFetcher implements RoleFetcher
{
    public function __construct(private UserRolesFetcher $fetcher)
    {
    }

    public function getUserRoles(UuidInterface $userUuid): UserRolesDto
    {
        /** @var UserRolesDto $roles */
        $roles = $this->fetcher->getUserRoles($userUuid);

        return $roles;
    }
}
