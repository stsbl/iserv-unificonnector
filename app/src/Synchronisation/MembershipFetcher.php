<?php

declare(strict_types=1);

namespace Stsbl\IServ\UnifiConnector\Synchronisation;

use IServ\Bundle\IdmDataBroker\Dto\UserGroupMembershipDto;
use IServ\Library\Uuid\UuidInterface;

interface MembershipFetcher
{
    public function fetch(UuidInterface $userUuid): UserGroupMembershipDto;
}
