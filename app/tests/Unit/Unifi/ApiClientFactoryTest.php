<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Unifi;

use IServ\UnifiConnector\Configuration\ConnectionConfiguration;
use IServ\UnifiConnector\Configuration\FileConfigurationRepository;
use IServ\UnifiConnector\Unifi\ApiClientFactory;
use IServ\UnifiConnector\Unifi\ApiKeyClient;
use IServ\UnifiConnector\Unifi\PasswordClientFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ApiClientFactory::class)]
final class ApiClientFactoryTest extends TestCase
{
    private string $path;
    private PasswordClientFactory $passwordClients;

    protected function setUp(): void
    {
        $this->path = tempnam(sys_get_temp_dir(), 'unificonnector-') ?: throw new \RuntimeException('Could not create temporary file.');
        unlink($this->path);
        $this->passwordClients = $this->createStub(PasswordClientFactory::class);
    }

    protected function tearDown(): void
    {
        @unlink($this->path);
    }

    public function testRequiresConfiguration(): void
    {
        $factory = new ApiClientFactory(new FileConfigurationRepository($this->path), $this->passwordClients);

        $this->expectExceptionMessage('UniFi Connector is not configured.');
        $factory->createApiClient();
    }

    public function testRequiresApiKeyWhenApiKeyAuthenticationIsSelected(): void
    {
        $repository = new FileConfigurationRepository($this->path);
        $repository->store(new ConnectionConfiguration('https://unifi.example.test', '', '', '', 'api_key'));
        $factory = new ApiClientFactory($repository, $this->passwordClients);

        $this->expectExceptionMessage('UniFi API key is not configured.');
        $factory->createApiClient();
    }

    public function testCreatesApiKeyClientWithoutLoggingIn(): void
    {
        $repository = new FileConfigurationRepository($this->path);
        $repository->store(new ConnectionConfiguration('unifi.example.test', '', '', '', 'api_key', 'secret'));

        self::assertInstanceOf(ApiKeyClient::class, (new ApiClientFactory($repository, $this->passwordClients))->createApiClient());
    }

    public function testRejectsInvalidUsernamePasswordLogin(): void
    {
        $repository = new FileConfigurationRepository($this->path);
        $repository->store(new ConnectionConfiguration('http://127.0.0.1:9', 'admin', 'wrong', '', 'password'));

        $client = $this->createMock(\UniFi_API\Client::class);
        $client->expects(self::once())->method('login')->willReturn(false);
        $passwordClients = $this->createMock(PasswordClientFactory::class);
        $passwordClients->expects(self::once())->method('create')->willReturn($client);

        $this->expectExceptionMessage('Login failed.');
        (new ApiClientFactory($repository, $passwordClients))->createApiClient();
    }

    public function testCreatesUsernamePasswordClientAfterSuccessfulLogin(): void
    {
        $repository = new FileConfigurationRepository($this->path);
        $repository->store(new ConnectionConfiguration('unifi.example.test', 'admin', 'password', '', 'password'));
        $client = $this->createMock(\UniFi_API\Client::class);
        $client->expects(self::once())->method('login')->willReturn(true);
        $passwordClients = $this->createMock(PasswordClientFactory::class);
        $passwordClients->expects(self::once())->method('create')->with('admin', 'password', 'https://unifi.example.test')->willReturn($client);

        self::assertSame($client, (new ApiClientFactory($repository, $passwordClients))->createApiClient());
    }

}
