<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use IServ\UnifiConnector\Entity\GroupAssignment;
use IServ\UnifiConnector\Entity\RoleAssignment;
use IServ\UnifiConnector\Entity\UniFiGroupMapping;
use IServ\UnifiConnector\Entity\UserAssignment;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UniFiGroupMapping::class)]
final class DoctrineMappingTest extends KernelTestCase
{
    public function testMappingSchemaContainsAllMappingTables(): void
    {
        self::bootKernel();
        $manager = self::getContainer()->get(EntityManagerInterface::class);
        $schema = (new SchemaTool($manager))->getCreateSchemaSql($manager->getMetadataFactory()->getAllMetadata());
        $sql = implode("\n", $schema);

        self::assertStringContainsString('unificonnector_usergroup', $sql);
        self::assertStringContainsString('unificonnector_usergroup_group', $sql);
        self::assertStringContainsString('unificonnector_usergroup_role', $sql);
        self::assertStringContainsString('unificonnector_usergroup_user', $sql);
    }
}
