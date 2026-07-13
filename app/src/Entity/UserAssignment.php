<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @psalm-suppress ClassMustBeFinal Doctrine creates proxies for entities. */
#[ORM\Entity]
#[ORM\Table(name: 'unificonnector_usergroup_user')]
class UserAssignment
{
    /** @psalm-suppress UnusedProperty Doctrine hydrates this association. */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: UniFiGroupMapping::class, inversedBy: 'userAssignments')]
    #[ORM\JoinColumn(name: 'usergroup_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UniFiGroupMapping $mapping;

    /** @psalm-suppress UnusedProperty Doctrine queries this identifier. */
    #[ORM\Id]
    #[ORM\Column(name: 'user_uuid', type: 'guid')]
    private string $userUuid;

    public function __construct(UniFiGroupMapping $mapping, string $userUuid)
    {
        $this->mapping = $mapping;
        $this->userUuid = $userUuid;
    }

    /** @psalm-suppress PossiblyUnusedMethod Exposed by the persistence entity. */
    public function userUuid(): string
    {
        return $this->userUuid;
    }

    /** @psalm-suppress PossiblyUnusedMethod Exposed by the persistence entity. */
    public function mapping(): UniFiGroupMapping
    {
        return $this->mapping;
    }
}
