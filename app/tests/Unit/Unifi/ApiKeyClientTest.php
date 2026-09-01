<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Unifi;

use IServ\UnifiConnector\Unifi\ApiKeyClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ApiKeyClient::class)]
final class ApiKeyClientTest extends TestCase
{
    public function testMarksTheUniFiOsClientAsAuthenticatedWithTheApiKeyHeader(): void
    {
        $client = new ApiKeyClient('https://unifi.example.test', 'secret');

        self::assertTrue($client->login());
        self::assertTrue($this->property($client, 'is_unifi_os'));
        self::assertTrue($this->property($client, 'is_logged_in'));
        self::assertContains('X-API-KEY: secret', $this->property($client, 'curl_headers'));
    }

    private function property(ApiKeyClient $client, string $name): mixed
    {
        $property = new \ReflectionProperty($client, $name);

        return $property->getValue($client);
    }
}
