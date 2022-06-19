<?php

declare(strict_types=1);

namespace Stsbl\IServ\Module\UnifiConnector\Host;

use IServ\Library\Database\DatabaseConnection;

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
final class DatabaseHostRepository implements HostRepository
{
    public function __construct(
        private readonly DatabaseConnection $databaseConnection,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(): iterable
    {
        $statement = $this->databaseConnection->prepare('SELECT name, ip, mac FROM hosts ORDER BY mac');

        foreach ($statement->fetchAll([]) as $row) {
            if (!array_key_exists('name', $row) || !array_key_exists('ip', $row) || !array_key_exists('mac', $row)) {
                throw new \RuntimeException('Invalid row!');
            }

            yield Host::fromDatabaseRow($row);
        }
    }
}
