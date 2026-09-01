<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Integration\Application\Mapping;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use IServ\Bundle\Autocomplete\Form\Data\AutocompleteTagsData;
use IServ\UnifiConnector\Application\Mapping\MappingManager;
use IServ\UnifiConnector\Application\Mapping\MappingSettings;
use IServ\UnifiConnector\Entity\UniFiGroupMapping;
use IServ\UnifiConnector\Repository\UniFiGroupMappingRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(MappingManager::class)]
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
        $settings->subjects = [new AutocompleteTagsData('userid:u', 'u', 'userid'), new AutocompleteTagsData('groupid:g', 'g', 'groupid'), new AutocompleteTagsData('roleid:r', 'r', 'roleid')];
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
}
