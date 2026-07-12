<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'unificonnector_usergroup_group')]
final class GroupAssignment
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: UniFiGroupMapping::class, inversedBy: 'groupAssignments')]
    #[ORM\JoinColumn(name: 'usergroup_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UniFiGroupMapping $mapping;

    #[ORM\Id]
    #[ORM\Column(name: 'group_uuid', type: 'guid')]
    private string $groupUuid;

    public function __construct(UniFiGroupMapping $mapping, string $groupUuid)
    {
        $this->mapping = $mapping;
        $this->groupUuid = $groupUuid;
    }
}
