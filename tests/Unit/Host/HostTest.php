<?php

declare(strict_types=1);

namespace Stsbl\IServ\Module\UnifiConnector\Tests\Unit\Host;

use Stsbl\IServ\Module\UnifiConnector\Host\Host;
use PHPUnit\Framework\TestCase;

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
 *
 * @covers \Stsbl\IServ\Module\UnifiConnector\Host\Host
 */

final class HostTest extends TestCase
{
    public function testGetIp(): void
    {
        $host = new Host('Schubidu', '1.2.3.4', 'af:af:af:af:af:af');

        $this->assertSame('1.2.3.4', $host->getIp(), 'IP is correct');
    }

    public function testGetMac(): void
    {
        $host = new Host('Schubidu', '1.2.3.4', 'af:af:af:af:af:af');

        $this->assertSame('af:af:af:af:af:af', $host->getMac(), 'MAC is correct');
    }

    public function testGetName(): void
    {
        $host = new Host('Schubidu', '1.2.3.4', 'af:af:af:af:af:af');

        $this->assertSame('Schubidu', $host->getName(), 'name is correct');
    }

    public function testFromDatabaseRow(): void
    {
        $host = Host::fromDatabaseRow(['name' => 'ImportedHost', 'ip' => '6.6.6.6', 'mac' => null]);

        $this->assertSame('ImportedHost', $host->getName(), 'name is correct');
        $this->assertSame('6.6.6.6', $host->getIp(), 'IP is correct');
        $this->assertNull($host->getMac(), 'MAC is correct');
    }
}
