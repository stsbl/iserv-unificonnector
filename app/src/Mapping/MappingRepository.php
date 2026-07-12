<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Mapping;

use IServ\UnifiConnector\Repository\UniFiGroupMappingRepository;

/** Resolves the first matching UniFi group using the module's Doctrine entities. */
final readonly class MappingRepository
{
    public function __construct(private UniFiGroupMappingRepository $mappings)
    {
    }

    /** @param list<string> $groupUuids @param list<string> $roleUuids */
    public function groupForMemberships(?string $userUuid, array $groupUuids, array $roleUuids): ?string
    {
        $query = $this->mappings->createQueryBuilder('mapping')
            ->select('mapping.id')
            ->leftJoin('mapping.groupAssignments', 'groupAssignment')
            ->leftJoin('mapping.roleAssignments', 'roleAssignment')
            ->leftJoin('mapping.userAssignments', 'userAssignment')
            ->orderBy('mapping.priority', 'ASC')
            ->setMaxResults(1);

        $predicates = [];
        if ([] !== $groupUuids) {
            $predicates[] = 'groupAssignment.groupUuid IN (:groups)';
            $query->setParameter('groups', $groupUuids);
        }
        if ([] !== $roleUuids) {
            $predicates[] = 'roleAssignment.roleUuid IN (:roles)';
            $query->setParameter('roles', $roleUuids);
        }
        if (null !== $userUuid) {
            $predicates[] = 'userAssignment.userUuid = :user';
            $query->setParameter('user', $userUuid);
        }
        if ([] === $predicates) {
            return null;
        }

        $result = $query->where(implode(' OR ', $predicates))->getQuery()->getOneOrNullResult();

        return null === $result ? null : (string) $result['id'];
    }
}
