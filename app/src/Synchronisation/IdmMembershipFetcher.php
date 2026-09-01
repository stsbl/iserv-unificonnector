<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Synchronisation;

use IServ\Bundle\IdmDataBroker\Dto\UserGroupMembershipDto;
use IServ\Bundle\IdmDataBroker\Service\UserGroupMembershipFetcher;
use IServ\Library\Uuid\UuidInterface;

final readonly class IdmMembershipFetcher implements MembershipFetcher
{
    public function __construct(private UserGroupMembershipFetcher $fetcher)
    {
    }

    public function fetch(UuidInterface $userUuid): UserGroupMembershipDto
    {
        return $this->fetcher->fetch($userUuid);
    }
}
