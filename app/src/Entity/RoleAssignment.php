<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @psalm-suppress ClassMustBeFinal Doctrine creates proxies for entities. */
#[ORM\Entity]
#[ORM\Table(name: 'unificonnector_usergroup_role')]
class RoleAssignment
{
    /** @psalm-suppress UnusedProperty Doctrine hydrates this association. */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: UniFiGroupMapping::class, inversedBy: 'roleAssignments')]
    #[ORM\JoinColumn(name: 'usergroup_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UniFiGroupMapping $mapping;

    /** @psalm-suppress UnusedProperty Doctrine queries this identifier. */
    #[ORM\Id]
    #[ORM\Column(name: 'role_uuid', type: 'guid')]
    private string $roleUuid;

    /** @psalm-suppress PossiblyUnusedMethod Instantiated by mapping persistence. */
    public function __construct(UniFiGroupMapping $mapping, string $roleUuid)
    {
        $this->mapping = $mapping;
        $this->roleUuid = $roleUuid;
    }

    /** @psalm-suppress PossiblyUnusedMethod Exposed by the persistence entity. */
    public function roleUuid(): string
    {
        return $this->roleUuid;
    }

    /** @psalm-suppress PossiblyUnusedMethod Exposed by the persistence entity. */
    public function mapping(): UniFiGroupMapping
    {
        return $this->mapping;
    }
}
