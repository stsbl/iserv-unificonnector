<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use IServ\UnifiConnector\Entity\UniFiGroupMapping;

/** @extends ServiceEntityRepository<UniFiGroupMapping> */
final class UniFiGroupMappingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UniFiGroupMapping::class);
    }

    /** @return list<UniFiGroupMapping> */
    public function findOrdered(): array
    {
        return $this->findBy([], ['priority' => 'ASC']);
    }

    public function nextTemporaryPriority(): int
    {
        $lowest = $this->createQueryBuilder('mapping')
            ->select('MIN(mapping.priority)')
            ->getQuery()
            ->getSingleScalarResult();

        return null === $lowest ? -1 : ((int) $lowest - 1);
    }
}
