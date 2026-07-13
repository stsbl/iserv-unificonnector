<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Unifi;

use IServ\UnifiConnector\Configuration\FileConfigurationRepository;
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

use UniFi_API\Client;

/**
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://opensource.org/licenses/MIT>
 */
final class ApiClientFactory
{
    public function __construct(
        private readonly FileConfigurationRepository $configurationRepository,
    ) {
    }

    public function createApiClient(): Client
    {
        $configuration = $this->configurationRepository->find();
        if (null === $configuration) {
            throw new \RuntimeException('UniFi Connector is not configured.');
        }

        $client = new Client($configuration->username, $configuration->password, 'https://' . $configuration->url, '', '', true);
        //$client->set_debug(true);
        if (false === $client->login()) {
            throw new \RuntimeException('Login failed.');
        }

        return $client;
    }
}
