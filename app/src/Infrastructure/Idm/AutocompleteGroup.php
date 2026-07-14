<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Infrastructure\Idm;

use IServ\Bundle\IdmDataBroker\Dto\Attribute\AsIdmDto;
use IServ\Bundle\IdmDataBroker\Dto\Attribute\IdmField;
use IServ\Bundle\IdmDataBroker\Dto\IdmCacheScope;

#[AsIdmDto(versions: [self::CURRENT_VERSION], scope: IdmCacheScope::Group, ttl: AsIdmDto::TTL_ONE_DAY)]
final class AutocompleteGroup
{
    public const CURRENT_VERSION = 1;

    public function __construct(
        #[IdmField('hexUuid')]
        public readonly string $uuid,
        #[IdmField('name')]
        public readonly ?string $name,
        #[IdmField('group')]
        public readonly ?string $account,
    ) {
    }

    public function displayName(): string
    {
        return $this->name ?? $this->account ?? $this->uuid;
    }
}
