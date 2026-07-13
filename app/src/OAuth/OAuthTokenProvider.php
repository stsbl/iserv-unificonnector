<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\OAuth;

use IServ\Library\Config\Config;
use Psr\Clock\ClockInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Service\ResetInterface;
use Symfony\Contracts\Service\Attribute\SubscribedService;

final class OAuthTokenProvider implements ResetInterface
{
    private ?string $token = null;
    private ?\DateTimeImmutable $expiresAt = null;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly ClockInterface $clock,
        #[AutowireLocator([new SubscribedService(type: Config::class)])]
        private readonly ContainerInterface $locator,
    ) {
    }

    public function token(): string
    {
        if (null !== $this->token && null !== $this->expiresAt && $this->expiresAt > $this->clock->now()->modify('+30 seconds')) {
            return $this->token;
        }

        $credentials = $this->credentials();
        $servername = $this->servername();
        $discovery = $this->client->request('GET', sprintf('https://%s/.well-known/openid-configuration', $servername))->toArray();
        $tokenEndpoint = $discovery['token_endpoint'] ?? null;
        if (!is_string($tokenEndpoint)) {
            throw new \RuntimeException('OpenID discovery document has no token endpoint.');
        }
        $response = $this->client->request('POST', $tokenEndpoint, ['body' => [
            'grant_type' => 'client_credentials',
            'client_id' => $credentials['clientId'],
            'client_secret' => $credentials['clientSecret'],
            'scope' => 'iserv:host:hosts:read iserv:idm:api-read',
        ]])->toArray();

        if (!isset($response['access_token'], $response['expires_in']) || !is_string($response['access_token']) || !is_numeric($response['expires_in'])) {
            throw new \RuntimeException('OAuth token endpoint returned an invalid token response.');
        }

        $this->token = $response['access_token'];
        $this->expiresAt = $this->clock->now()->modify(sprintf('+%d seconds', (int) $response['expires_in']));

        return $this->token;
    }

    public function reset(): void
    {
        $this->token = null;
        $this->expiresAt = null;
    }

    /** @return array{clientId: string, clientSecret: string} */
    private function credentials(): array
    {
        /** @var array{clientId: string, clientSecret: string} $credentials */
        $credentials = json_decode((string) file_get_contents('/var/lib/iserv/auth/credentials/iserv_unificonnector.json'), true, 512, JSON_THROW_ON_ERROR);

        return $credentials;
    }

    private function servername(): string
    {
        try {
            /** @psalm-suppress PrivateService Config is exposed through this service locator. */
            $config = $this->locator->get(Config::class);
        } catch (ContainerExceptionInterface|NotFoundExceptionInterface $exception) {
            throw new \RuntimeException('Could not load IServ configuration.', previous: $exception);
        }
        if (!$config instanceof Config) {
            throw new \RuntimeException('IServ configuration service has an unexpected type.');
        }

        return $config->getString('Servername');
    }
}
