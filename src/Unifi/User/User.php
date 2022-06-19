<?php

declare(strict_types=1);

namespace Stsbl\IServ\Module\UnifiConnector\Unifi\User;

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
final class User
{
    public function __construct(
        private readonly ?string $id,
        private string $name,
        private string $mac,
        private ?string $groupId = null,
    ) {
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMac(): string
    {
        return $this->mac;
    }

    public function setMac(string $mac): void
    {
        $this->mac = $mac;
    }

    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    public function setGroupId(?string $groupId): void
    {
        $this->groupId = $groupId;
    }

    public function equals(self $that): bool
    {
        return $this->name === $that->getName() &&
            strtolower($this->mac) === strtolower($that->getMac()) &&
            $this->groupId === $that->getGroupId()
        ;
    }

    public function updateFrom(self $that): void
    {
        $this->name = $that->getName();
        $this->groupId = $that->getGroupId();
    }

    /**
     * @param array{_id: string, name: string, mac: string, groupId: string} $user
     */
    public static function fromApiResponse(array $user): self
    {
        $groupId = $user['groupId'] === '' ? null : $user['groupId'];

        return new self(
            $user['_id'],
            $user['name'],
            $user['mac'],
            $groupId,
        );
    }
}
