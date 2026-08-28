<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Unifi;

use IServ\UnifiConnector\Unifi\UnifiUrl;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnifiUrl::class)]
final class UnifiUrlTest extends TestCase
{
    #[DataProvider('urlProvider')]
    public function testCreatesHttpsUrl(string $input, string $expected): void
    {
        self::assertSame($expected, UnifiUrl::fromString($input)->value);
    }

    /** @return iterable<string, array{string, string}> */
    public static function urlProvider(): iterable
    {
        yield 'host' => ['unifi.example.test', 'https://unifi.example.test'];
        yield 'http URL' => ['http://unifi.example.test', 'https://unifi.example.test'];
        yield 'https URL' => ['https://unifi.example.test', 'https://unifi.example.test'];
        yield 'URL with path and trailing slash' => ['https://unifi.example.test/network/', 'https://unifi.example.test/network'];
    }
}
