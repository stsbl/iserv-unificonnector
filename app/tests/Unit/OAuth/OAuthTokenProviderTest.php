<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\OAuth;

use IServ\Library\Config\Config;
use IServ\Library\Zeit\Clock\FixedClock;
use IServ\UnifiConnector\OAuth\OAuthTokenProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(OAuthTokenProvider::class)]
final class OAuthTokenProviderTest extends TestCase
{
    private string $credentialsPath;

    protected function setUp(): void
    {
        $this->credentialsPath = tempnam(sys_get_temp_dir(), 'unificonnector-oauth-');
        file_put_contents($this->credentialsPath, json_encode(['clientId' => 'client-id', 'clientSecret' => 'client-secret'], JSON_THROW_ON_ERROR));
    }

    protected function tearDown(): void
    {
        @unlink($this->credentialsPath);
    }

    public function testDiscoversAndCachesAccessTokenUntilItApproachesExpiry(): void
    {
        $now = new \DateTimeImmutable('2026-09-01T10:00:00+00:00');
        $clock = FixedClock::fromDateTime($now);
        $locator = $this->createMock(ContainerInterface::class);
        $locator->method('get')->with(Config::class)->willReturn(new Config(['Servername' => 'iserv.test']));
        $discovery = $this->createMock(ResponseInterface::class);
        $discovery->method('toArray')->willReturn(['token_endpoint' => 'https://iserv.test/token']);
        $token = $this->createMock(ResponseInterface::class);
        $token->method('toArray')->willReturn(['access_token' => 'access-token', 'expires_in' => 3600]);
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects(self::exactly(2))->method('request')->willReturnMap([
            ['GET', 'https://iserv.test/.well-known/openid-configuration', [], $discovery],
            ['POST', 'https://iserv.test/token', ['body' => ['grant_type' => 'client_credentials', 'client_id' => 'client-id', 'client_secret' => 'client-secret', 'scope' => 'iserv:host:hosts:read iserv:idm:api-read']], $token],
        ]);
        $provider = new OAuthTokenProvider($client, $clock, $locator, $this->credentialsPath);

        self::assertSame('access-token', $provider->token());
        self::assertSame('access-token', $provider->token());

        $provider->reset();
        self::assertNull((new \ReflectionProperty($provider, 'token'))->getValue($provider));
    }

    public function testRejectsDiscoveryDocumentWithoutTokenEndpoint(): void
    {
        $clock = FixedClock::fromString('2026-09-01T10:00:00+00:00');
        $locator = $this->createMock(ContainerInterface::class);
        $locator->method('get')->with(Config::class)->willReturn(new Config(['Servername' => 'iserv.test']));
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn([]);
        $client = $this->createMock(HttpClientInterface::class);
        $client->method('request')->willReturn($response);
        $provider = new OAuthTokenProvider($client, $clock, $locator, $this->credentialsPath);

        $this->expectExceptionMessage('OpenID discovery document has no token endpoint.');
        $provider->token();
    }

    public function testRejectsInvalidTokenResponse(): void
    {
        $clock = FixedClock::fromString('2026-09-01T10:00:00+00:00');
        $locator = $this->createMock(ContainerInterface::class);
        $locator->method('get')->with(Config::class)->willReturn(new Config(['Servername' => 'iserv.test']));
        $discovery = $this->createMock(ResponseInterface::class);
        $discovery->method('toArray')->willReturn(['token_endpoint' => 'https://iserv.test/token']);
        $token = $this->createMock(ResponseInterface::class);
        $token->method('toArray')->willReturn(['access_token' => 'access-token']);
        $client = $this->createMock(HttpClientInterface::class);
        $client->method('request')->willReturnOnConsecutiveCalls($discovery, $token);
        $provider = new OAuthTokenProvider($client, $clock, $locator, $this->credentialsPath);

        $this->expectExceptionMessage('OAuth token endpoint returned an invalid token response.');
        $provider->token();
    }

    public function testRejectsUnavailableIservConfiguration(): void
    {
        $clock = FixedClock::fromString('2026-09-01T10:00:00+00:00');
        $locator = $this->createMock(ContainerInterface::class);
        $locator->method('get')->with(Config::class)->willThrowException($this->createMock(NotFoundExceptionInterface::class));
        $client = $this->createMock(HttpClientInterface::class);
        $provider = new OAuthTokenProvider($client, $clock, $locator, $this->credentialsPath);

        $this->expectExceptionMessage('Could not load IServ configuration.');
        $provider->token();
    }

    public function testRejectsUnexpectedIservConfigurationType(): void
    {
        $clock = FixedClock::fromString('2026-09-01T10:00:00+00:00');
        $locator = $this->createMock(ContainerInterface::class);
        $locator->method('get')->with(Config::class)->willReturn(new \stdClass());
        $client = $this->createMock(HttpClientInterface::class);
        $provider = new OAuthTokenProvider($client, $clock, $locator, $this->credentialsPath);

        $this->expectExceptionMessage('IServ configuration service has an unexpected type.');
        $provider->token();
    }
}
