<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Integration\Application\Mapping;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use IServ\Bundle\Autocomplete\Form\Data\AutocompleteTagsData;
use IServ\UnifiConnector\Application\Mapping\MappingManager;
use IServ\UnifiConnector\Application\Mapping\MappingSettings;
use IServ\UnifiConnector\Entity\UniFiGroupMapping;
use IServ\UnifiConnector\Mapping\MappingRepository;
use IServ\UnifiConnector\Repository\UniFiGroupMappingRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(MappingManager::class)]
#[CoversClass(MappingRepository::class)]
#[CoversClass(UniFiGroupMappingRepository::class)]
final class MappingManagerTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UniFiGroupMappingRepository $repository;
    private MappingManager $manager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $schema = new SchemaTool($this->entityManager);
        $schema->dropSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
        $schema->createSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
        $this->repository = $this->entityManager->getRepository(UniFiGroupMapping::class);
        $this->manager = new MappingManager($this->entityManager, $this->repository);
    }

    public function testCreatesMappingForEachSupportedSubjectType(): void
    {
        $settings = new MappingSettings();
        $settings->id = 'group';
        $settings->name = 'Teachers';
        $settings->priority = 2;
        $settings->subjects = [
            new AutocompleteTagsData('userid:u', 'u', 'userid'),
            new AutocompleteTagsData('groupid:g', 'g', 'groupid'),
            new AutocompleteTagsData('roleid:r', 'r', 'roleid'),
            new AutocompleteTagsData('unsupported:ignored', 'ignored', 'unsupported'),
            new AutocompleteTagsData('missing-id', null, 'userid'),
        ];
        $this->manager->create($settings);

        $persisted = $this->repository->find('group');
        self::assertInstanceOf(UniFiGroupMapping::class, $persisted);
        self::assertCount(1, $persisted->roleAssignments());
    }

    public function testDeletesMapping(): void
    {
        $mapping = new UniFiGroupMapping('group', 'Teachers', 1);
        $this->entityManager->persist($mapping);
        $this->entityManager->flush();
        $this->manager->delete($mapping);

        self::assertNull($this->repository->find('group'));
    }

    public function testMovesMappingBySwappingPriorities(): void
    {
        $first = new UniFiGroupMapping('first', 'First', 1);
        $second = new UniFiGroupMapping('second', 'Second', 2);
        $this->entityManager->persist($first);
        $this->entityManager->persist($second);
        $this->entityManager->flush();

        $this->manager->move($first, 'down');

        self::assertSame(2, $first->priority());
        self::assertSame(1, $second->priority());
    }

    public function testIgnoresMoveOutsideTheOrderedMappingList(): void
    {
        $unpersisted = new UniFiGroupMapping('unpersisted', 'Unpersisted', 1);

        $this->manager->move($unpersisted, 'up');
        $this->manager->move($unpersisted, 'down');

        self::assertSame(1, $unpersisted->priority());
    }

    public function testIgnoresMovesBeyondTheFirstAndLastMappings(): void
    {
        $first = new UniFiGroupMapping('first', 'First', 1);
        $last = new UniFiGroupMapping('last', 'Last', 2);
        $this->entityManager->persist($first);
        $this->entityManager->persist($last);
        $this->entityManager->flush();

        $this->manager->move($first, 'up');
        $this->manager->move($last, 'down');

        self::assertSame(1, $first->priority());
        self::assertSame(2, $last->priority());
    }

    public function testResolvesTheLowestPriorityMappingForUserGroupAndRoleAssignments(): void
    {
        $group = new UniFiGroupMapping('group', 'Groups', 3);
        $group->addGroupAssignment('48bd3d36-77e8-4073-bac2-a7d96fc2f6ba');
        $role = new UniFiGroupMapping('role', 'Roles', 2);
        $role->addRoleAssignment('c2bb15be-ea9d-447d-9c49-5c719ff82059');
        $user = new UniFiGroupMapping('user', 'Users', 1);
        $user->addUserAssignment('1af6f295-66f4-4af8-a924-b41c531ebe11');
        $this->entityManager->persist($group);
        $this->entityManager->persist($role);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $mappings = new MappingRepository($this->repository);

        self::assertSame('user', $mappings->groupForMemberships('1af6f295-66f4-4af8-a924-b41c531ebe11', ['48bd3d36-77e8-4073-bac2-a7d96fc2f6ba'], ['c2bb15be-ea9d-447d-9c49-5c719ff82059']));
        self::assertSame('role', $mappings->groupForMemberships(null, [], ['c2bb15be-ea9d-447d-9c49-5c719ff82059']));
        self::assertSame('group', $mappings->groupForMemberships(null, ['48bd3d36-77e8-4073-bac2-a7d96fc2f6ba'], []));
        self::assertNull($mappings->groupForMemberships(null, ['00000000-0000-0000-0000-000000000000'], []));
        self::assertNull($mappings->groupForMemberships(null, [], []));
    }
}
