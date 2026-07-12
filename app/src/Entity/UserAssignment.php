<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'unificonnector_usergroup_user')]
final class UserAssignment
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: UniFiGroupMapping::class, inversedBy: 'userAssignments')]
    #[ORM\JoinColumn(name: 'usergroup_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UniFiGroupMapping $mapping;

    #[ORM\Id]
    #[ORM\Column(name: 'user_uuid', type: 'guid')]
    private string $userUuid;

    public function __construct(UniFiGroupMapping $mapping, string $userUuid)
    {
        $this->mapping = $mapping;
        $this->userUuid = $userUuid;
    }
}
