<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Application\Mapping;

use Doctrine\ORM\EntityManagerInterface;
use IServ\Bundle\Autocomplete\Form\Data\AutocompleteTagsData;
use IServ\UnifiConnector\Entity\UniFiGroupMapping;
use IServ\UnifiConnector\Repository\UniFiGroupMappingRepository;

/** Application service for the ordered mapping aggregate. */
final readonly class MappingManager
{
    public function __construct(private EntityManagerInterface $entityManager, private UniFiGroupMappingRepository $mappings)
    {
    }

    public function create(MappingSettings $settings): void
    {
        $mapping = new UniFiGroupMapping($settings->id, $settings->name, $settings->priority);
        foreach ($settings->subjects as $subject) {
            if (!$subject instanceof AutocompleteTagsData || null === $id = $subject->getId()) {
                continue;
            }
            match ($subject->getSource()) {
                'userid' => $mapping->addUserAssignment($id),
                'groupid' => $mapping->addGroupAssignment($id),
                default => null,
            };
        }
        $this->entityManager->persist($mapping);
        $this->entityManager->flush();
    }

    public function delete(UniFiGroupMapping $mapping): void
    {
        $this->entityManager->remove($mapping);
        $this->entityManager->flush();
    }

    public function move(UniFiGroupMapping $mapping, string $direction): void
    {
        $ordered = $this->mappings->findOrdered();
        $index = array_search($mapping, $ordered, true);
        if (false === $index) {
            return;
        }
        if ('up' === $direction && 0 === $index) {
            return;
        }
        $other = 'up' === $direction ? $ordered[$index - 1] : $ordered[$index + 1] ?? null;
        if (!$other instanceof UniFiGroupMapping) {
            return;
        }

        $this->entityManager->wrapInTransaction(function () use ($mapping, $other): void {
            $currentPriority = $mapping->priority();
            $otherPriority = $other->priority();
            // Flush via an unused value first: PostgreSQL checks this unique constraint immediately.
            $mapping->setPriority($this->mappings->nextTemporaryPriority());
            $this->entityManager->flush();
            $other->setPriority($currentPriority);
            $this->entityManager->flush();
            $mapping->setPriority($otherPriority);
            $this->entityManager->flush();
        });
    }
}
