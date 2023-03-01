<?php

declare(strict_types=1);

namespace Stsbl\IServ\Module\UnifiConnector\Sychronisation;

use Stsbl\IServ\Module\UnifiConnector\Host\HostRepository;
use Stsbl\IServ\Module\UnifiConnector\Unifi\User\User;
use Stsbl\IServ\Module\UnifiConnector\Unifi\User\UserRepository;
use Stsbl\IServ\Module\UnifiConnector\Unifi\UserGroup\UserGroup;
use Stsbl\IServ\Module\UnifiConnector\Unifi\UserGroup\UserGroupRepository;
use Symfony\Component\Console\Command\Command;
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
final class SyncCommand extends Command
{
    private const DEFAULT_GROUP = 'Default';

    protected static $defaultName = 'app:sync';

    /**
     * @var array<string, ?UserGroup>
     */
    private array $userGroups = [];

    public function __construct(
        private readonly UserGroupRepository $userGroupRepository,
        private readonly HostRepository $hostRepository,
        private readonly UserRepository $userRepository,
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
        $userGroup = $this->userGroup(self::DEFAULT_GROUP);

        foreach ($this->userRepository->findAll() as $client) {
            $existingClients[$client->getMac()] = $client;
        }

        foreach ($this->hostRepository->findAll() as $host) {
            if (null === $host->getMac()) {
                continue;
            }

            $client = $host->toClient();
            $client->setGroupId($userGroup?->getId());

            if (!isset($existingClients[$host->getMac()]) || !$existingClients[$host->getMac()]->equals($client)) {
                $output->writeln(sprintf('Syncing host "%s" to UniFi...', $host->getName()), OutputInterface::VERBOSITY_VERBOSE);
                $saveClient = $existingClients[$host->getMac()] ?? $client;
                $saveClient->updateFrom($client);

                $this->userRepository->save($saveClient);
            }
        }

        return 0;
    }

    private function userGroup(string $name): ?UserGroup
    {
        if (!\array_key_exists($name, $this->userGroups)) {
            $this->userGroups[$name] = $this->userGroupRepository->findByName($name);
        }

        return $this->userGroups[$name];
    }
}
