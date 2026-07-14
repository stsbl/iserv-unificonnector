<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Infrastructure\Idm;

use IServ\Bundle\IdmDataBroker\Dto\Attribute\AsIdmDto;
use IServ\Bundle\IdmDataBroker\Dto\Attribute\IdmField;
use IServ\Bundle\IdmDataBroker\Dto\IdmCacheScope;

#[AsIdmDto(versions: [self::CURRENT_VERSION], scope: IdmCacheScope::User, ttl: AsIdmDto::TTL_ONE_DAY)]
final class AutocompleteUser
{
    public const CURRENT_VERSION = 1;

    public function __construct(
        #[IdmField('hexUuid')]
        public readonly string $uuid,
        #[IdmField('user')]
        public readonly ?string $account,
        #[IdmField('firstname')]
        public readonly ?string $firstName,
        #[IdmField('lastname')]
        public readonly ?string $lastName,
        #[IdmField('auxInfo')]
        public readonly ?string $auxInfo,
    ) {
    }

    public function displayName(): string
    {
        return trim(implode(' ', array_filter([$this->firstName, $this->lastName]))) ?: ($this->account ?? $this->uuid);
    }
}
