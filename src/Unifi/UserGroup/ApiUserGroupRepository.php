<?php

declare(strict_types=1);

namespace Stsbl\IServ\Module\UnifiConnector\Unifi\UserGroup;

use UniFi_API\Client as UniFiApiClient;

/*
 * The MIT License
 *
 * Copyright 2022 Felix Jacobi.
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
final class ApiUserGroupRepository implements UserGroupRepository
{
    public function __construct(
        private readonly UniFiApiClient $apiClient,
    )
    {
    }

    public function findByName(string $name): ?UserGroup
    {
        /** @var list<array{_id: string, site_id: string, name: string}>|false $userGroupData */
        $userGroupData = $this->apiClient->list_usergroups();

        if (false === $userGroupData) {
            return null;
        }

        foreach ($userGroupData as $groupData) {
            $group = UserGroup::fromApiResponse((array)$groupData);

            if ($group->getName() === $name) {
                return $group;
            }
        }

        return null;
    }
}
