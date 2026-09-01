<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Synchronisation;

use IServ\Bundle\IdmDataBroker\Dto\UserGroupMembershipDto;
use IServ\Bundle\IdmDataBroker\Dto\UserRolesDto;
use IServ\Library\Uuid\Uuid;
use IServ\UnifiConnector\Configuration\ConnectionConfiguration;
use IServ\UnifiConnector\Configuration\FileConfigurationRepository;
use IServ\UnifiConnector\Host\Host;
use IServ\UnifiConnector\Host\HostRepository;
use IServ\UnifiConnector\Mapping\MappingResolver;
use IServ\UnifiConnector\Synchronisation\MembershipFetcher;
use IServ\UnifiConnector\Synchronisation\RoleFetcher;
use IServ\UnifiConnector\Synchronisation\SyncCommand;
use IServ\UnifiConnector\Unifi\User\User;
use IServ\UnifiConnector\Unifi\User\UserRepository;
use IServ\UnifiConnector\Unifi\UserGroup\UserGroup;
use IServ\UnifiConnector\Unifi\UserGroup\UserGroupRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(SyncCommand::class)]
final class SyncCommandTest extends TestCase
{
    private string $configurationPath;

    protected function setUp(): void
    {
        $this->configurationPath = tempnam(sys_get_temp_dir(), 'unificonnector-') ?: throw new \RuntimeException('Could not create temporary file.');
        unlink($this->configurationPath);
    }

    protected function tearDown(): void
    {
        @unlink($this->configurationPath);
    }

    public function testSynchronizesOnlyChangedHostsAndUsesUuidRoleMappings(): void
    {
        $owner = Uuid::createFromString('e5897a99-0f09-4563-8a17-93567f7a58b8');
        $hosts = $this->createMock(HostRepository::class);
        $hosts->method('findAll')->willReturn([
            new Host('without-mac', 'No MAC', '10.0.0.1', null),
            new Host('changed', 'Printer', '10.0.0.2', 'aa:bb:cc:dd:ee:ff', $owner->toNormalizedString()),
            new Host('unchanged', 'Unchanged', '10.0.0.3', '11:22:33:44:55:66'),
        ]);
        $users = $this->createMock(UserRepository::class);
        $users->method('findAll')->willReturn([
            new User('existing', 'Old printer', 'aa:bb:cc:dd:ee:ff'),
            new User('same', 'Unchanged', '11:22:33:44:55:66'),
        ]);
        $users->expects(self::once())->method('save')->with(self::callback(static fn(User $user): bool => 'Printer' === $user->getName() && 'group-id' === $user->getGroupId()));
        $groups = $this->createMock(UserGroupRepository::class);
        $groups->method('findByName')->willReturnCallback(static fn(string $name): ?UserGroup => 'uni-group' === $name ? new UserGroup('group-id', 'site', 'uni-group') : null);
        $mappings = $this->createMock(MappingResolver::class);
        $mappings->expects(self::exactly(2))->method('groupForMemberships')->willReturnCallback(static function (?string $userUuid, array $groupUuids, array $roleUuids) use ($owner): ?string {
            if ($owner->toNormalizedString() === $userUuid) {
                self::assertSame(['31787b3d-079e-4b18-a405-4806bd03e11e'], $groupUuids);
                self::assertSame(['ec88160b-cd5f-4f77-a0d8-7f1be23385e2'], $roleUuids);

                return 'uni-group';
            }

            return null;
        });
        $memberships = $this->createMock(MembershipFetcher::class);
        $memberships->method('fetch')->willReturn(new UserGroupMembershipDto($owner, [Uuid::createFromString('31787b3d-079e-4b18-a405-4806bd03e11e')]));
        $roles = $this->createMock(RoleFetcher::class);
        $roles->method('getUserRoles')->willReturn(new UserRolesDto(['ROLE_LEGACY' => 'ec88160b-cd5f-4f77-a0d8-7f1be23385e2']));
        $configuration = new FileConfigurationRepository($this->configurationPath);
        $configuration->store(new ConnectionConfiguration('https://unifi.example.test', '', '', 'fallback'));

        $tester = new CommandTester(new SyncCommand($groups, $hosts, $users, $mappings, $configuration, $memberships, $roles));
        self::assertSame(0, $tester->execute([], ['verbosity' => 64]));

        self::assertStringContainsString('Syncing host "Printer"', $tester->getDisplay());
        self::assertStringNotContainsString('Syncing host "Unchanged"', $tester->getDisplay());
        self::assertStringContainsString('1 host(s) updated.', $tester->getDisplay());
    }

    public function testRequiresConfigurationBeforeSynchronizing(): void
    {
        $command = new SyncCommand($this->createMock(UserGroupRepository::class), $this->createMock(HostRepository::class), $this->createMock(UserRepository::class), $this->createMock(MappingResolver::class), new FileConfigurationRepository($this->configurationPath), $this->createMock(MembershipFetcher::class), $this->createMock(RoleFetcher::class));

        $this->expectExceptionMessage('UniFi Connector is not configured.');
        (new CommandTester($command))->execute([]);
    }
}
