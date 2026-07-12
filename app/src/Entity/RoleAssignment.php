<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'unificonnector_usergroup_role')]
final class RoleAssignment
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: UniFiGroupMapping::class, inversedBy: 'roleAssignments')]
    #[ORM\JoinColumn(name: 'usergroup_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UniFiGroupMapping $mapping;

    #[ORM\Id]
    #[ORM\Column(name: 'role_uuid', type: 'guid')]
    private string $roleUuid;

    public function __construct(UniFiGroupMapping $mapping, string $roleUuid)
    {
        $this->mapping = $mapping;
        $this->roleUuid = $roleUuid;
    }
}
