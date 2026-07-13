<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Synchronisation;

use IServ\UnifiConnector\Configuration\FileConfigurationRepository;
use IServ\UnifiConnector\Host\HostRepository;
use IServ\UnifiConnector\Mapping\MappingRepository;
use IServ\Bundle\IdmDataBroker\Service\UserGroupMembershipFetcher;
use IServ\Bundle\IdmDataBroker\Service\UserRolesFetcher;
use IServ\Bundle\IdmDataBroker\Dto\UserRolesDto;
use IServ\Library\Uuid\Uuid;
use IServ\UnifiConnector\Unifi\User\User;
use IServ\UnifiConnector\Unifi\User\UserRepository;
use IServ\UnifiConnector\Unifi\UserGroup\UserGroupRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/*
 * The MIT License
 *
 * Copyright 2021 Felix Jacobi.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://opensource.org/licenses/MIT>
 */
#[AsCommand(name: 'unificonnector:sync')]
final class SyncCommand extends Command
{
    public function __construct(
        private readonly UserGroupRepository $userGroupRepository,
        private readonly HostRepository $hostRepository,
        private readonly UserRepository $userRepository,
        private readonly MappingRepository $mappingRepository,
        private readonly FileConfigurationRepository $configurationRepository,
        private readonly UserGroupMembershipFetcher $groupMemberships,
        private readonly UserRolesFetcher $roles,
    ) {

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var User[] $existingClients */
        $existingClients = [];
        $configuration = $this->configurationRepository->find();
        if (null === $configuration) {
            throw new \RuntimeException('UniFi Connector is not configured.');
        }

        $fallbackGroup = $this->userGroupRepository->findByName($configuration->fallbackGroup);

        foreach ($this->userRepository->findAll() as $client) {
            $existingClients[$client->getMac()] = $client;
        }

        foreach ($this->hostRepository->findAll() as $host) {
            $mac = $host->getMac();
            if (null === $mac) {
                continue;
            }

            $client = $host->toClient();
            $groupUuids = [];
            $roleUuids = [];
            if (null !== $ownerUuid = $host->getOwnerUuid()) {
                $owner = Uuid::createFromString($ownerUuid);
                $groupUuids = array_map(static fn($uuid): string => $uuid->toNormalizedString(), $this->groupMemberships->fetch($owner)->groupUuids);
                $roles = $this->roles->getUserRoles($owner);
                if (!$roles instanceof UserRolesDto) {
                    throw new \LogicException('User roles fetcher returned an unexpected DTO.');
                }
                $roleUuids = $roles->roles();
            }
            $configuredGroup = $this->mappingRepository->groupForMemberships($ownerUuid, $groupUuids, $roleUuids);
            $userGroup = null === $configuredGroup ? $fallbackGroup : $this->userGroupRepository->findByName($configuredGroup);
            $client->setGroupId($userGroup?->getId());

            if (!isset($existingClients[$mac]) || !$existingClients[$mac]->equals($client)) {
                $output->writeln(sprintf('Syncing host "%s" to UniFi...', $host->getName()), OutputInterface::VERBOSITY_VERBOSE);
                $saveClient = $existingClients[$mac] ?? $client;
                $saveClient->updateFrom($client);

                $this->userRepository->save($saveClient);
            }
        }

        return 0;
    }
}
