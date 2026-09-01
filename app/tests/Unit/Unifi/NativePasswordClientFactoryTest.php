<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Unifi;

use IServ\UnifiConnector\Unifi\NativePasswordClientFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NativePasswordClientFactory::class)]
final class NativePasswordClientFactoryTest extends TestCase
{
    public function testCreatesPasswordAuthenticatedUniFiClient(): void
    {
        self::assertInstanceOf(\UniFi_API\Client::class, (new NativePasswordClientFactory())->create('admin', 'password', 'https://unifi.example.test'));
    }
}
