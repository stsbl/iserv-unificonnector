<?php

declare(strict_types=1);

namespace Stsbl\IServ\Module\UnifiConnector\Unifi\UserGroup;

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
final class UserGroup
{
    public function __construct(
        private readonly string $id,
        private readonly string $siteId,
        private readonly string $name,
    )
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSiteId(): string
    {
        return $this->siteId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param array{_id: string, site_id: string, name: string} $userGroup
     */
    public static function fromApiResponse(array $userGroup): self
    {
        return new self(
            $userGroup['_id'],
            $userGroup['site_id'],
            $userGroup['name'],
        );
    }

}
