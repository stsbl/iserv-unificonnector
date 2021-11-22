<?php

declare(strict_types=1);

namespace Stsbl\IServ\Module\UnifiConnector\Unifi\Client;

use UniFi_API\Client as UniFiApiClient;

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
final class ApiClientRepository implements ClientRepository
{
    public function __construct(
        private UniFiApiClient $apiClient,
    ) {
    }

    public function findAll(): iterable
    {
        /** @var list<object{mac: string, hostname: string, _id: string}>|false $userData */
        $userData = $this->apiClient->list_users();

        if (false === $userData) {
            return;
        }

        foreach ($userData as $user) {
            yield Client::fromApiResponse((array)$user);
        }
    }

    public function save(Client $client): void
    {
        // Client already existent, only update the name
        if (null !== $id = $client->getId()) {
            $this->apiClient->edit_client_name($id, $client->getName());
        } else {
            // @user_group_id: TODO
            $this->apiClient->create_user($client->getMac(), '58d2e6b8edd72092af3a4731', $client->getName());
        }
    }
}
