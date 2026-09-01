<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Synchronisation;

use IServ\Bundle\IdmDataBroker\Dto\UserGroupMembershipDto;
use IServ\Library\Uuid\UuidInterface;

interface MembershipFetcher
{
    public function fetch(UuidInterface $userUuid): UserGroupMembershipDto;
}
