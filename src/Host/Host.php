<?php

declare(strict_types=1);

namespace Stsbl\IServ\Module\UnifiConnector\Host;

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

use Stsbl\IServ\Module\UnifiConnector\Unifi\User\User;

/**
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://opensource.org/licenses/MIT>
 */
final class Host
{
    public function __construct(
        private readonly string $name,
        private readonly string $ip,
        private readonly ?string $mac,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getMac(): ?string
    {
        return $this->mac;
    }

    public function toClient(): User
    {
        if (null === $this->mac) {
            throw new \RuntimeException('A host without MAC cannot be converted to a client.');
        }

        return new User(null, $this->name, $this->mac);
    }

    /**
     * @param array{name: string, ip: string, mac: ?string} $row
     */
    public static function fromDatabaseRow(array $row): self
    {
        return new self(
            $row['name'],
            $row['ip'],
            $row['mac']
        );
    }
}
