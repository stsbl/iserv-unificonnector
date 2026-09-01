<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Configuration;

use IServ\UnifiConnector\Configuration\ConnectionConfiguration;
use IServ\UnifiConnector\Configuration\FileConfigurationRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConnectionConfiguration::class)]
#[CoversClass(FileConfigurationRepository::class)]
final class FileConfigurationRepositoryTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = tempnam(sys_get_temp_dir(), 'unificonnector-') ?: throw new \RuntimeException('Could not create temporary file.');
        unlink($this->path);
    }

    protected function tearDown(): void
    {
        @unlink($this->path);
    }

    public function testStoresAndReadsConfiguration(): void
    {
        $repository = new FileConfigurationRepository($this->path);
        self::assertNull($repository->find());

        $configuration = new ConnectionConfiguration('https://unifi.example', 'admin', 'secret', 'fallback', 'api_key', 'key');
        $repository->store($configuration);

        self::assertSame($configuration->toArray(), $repository->find()?->toArray());
    }

    public function testReadsLegacyPasswordConfiguration(): void
    {
        file_put_contents($this->path, json_encode(['url' => 'https://unifi.example', 'username' => 'admin', 'password' => 'secret', 'fallbackGroup' => 'fallback'], JSON_THROW_ON_ERROR));

        self::assertSame('password', (new FileConfigurationRepository($this->path))->find()?->authenticationMode);
    }
}
