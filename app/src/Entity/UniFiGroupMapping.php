<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use IServ\UnifiConnector\Repository\UniFiGroupMappingRepository;

#[ORM\Entity(repositoryClass: UniFiGroupMappingRepository::class)]
#[ORM\Table(name: 'unificonnector_usergroup')]
final class UniFiGroupMapping
{
    #[ORM\Id]
    #[ORM\Column(type: 'text')]
    private string $id;

    #[ORM\Column(type: 'text')]
    private string $name;

    #[ORM\Column(type: 'integer', unique: true)]
    private int $priority;

    /** @var Collection<int, GroupAssignment> */
    #[ORM\OneToMany(mappedBy: 'mapping', targetEntity: GroupAssignment::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $groupAssignments;

    /** @var Collection<int, RoleAssignment> */
    #[ORM\OneToMany(mappedBy: 'mapping', targetEntity: RoleAssignment::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $roleAssignments;

    /** @var Collection<int, UserAssignment> */
    #[ORM\OneToMany(mappedBy: 'mapping', targetEntity: UserAssignment::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $userAssignments;

    public function __construct(string $id, string $name, int $priority)
    {
        $this->id = $id;
        $this->name = $name;
        $this->priority = $priority;
        $this->groupAssignments = new ArrayCollection();
        $this->roleAssignments = new ArrayCollection();
        $this->userAssignments = new ArrayCollection();
    }

    public function id(): string { return $this->id; }
    public function name(): string { return $this->name; }
    public function priority(): int { return $this->priority; }
    public function setPriority(int $priority): void { $this->priority = $priority; }
    public function addGroupAssignment(string $groupUuid): void { $this->groupAssignments->add(new GroupAssignment($this, $groupUuid)); }
    public function addUserAssignment(string $userUuid): void { $this->userAssignments->add(new UserAssignment($this, $userUuid)); }
}
