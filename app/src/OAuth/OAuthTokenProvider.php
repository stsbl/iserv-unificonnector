<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\OAuth;

use IServ\Library\Config\Config;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Service\Attribute\SubscribedService;

final class OAuthTokenProvider
{
    private ?string $token = null;

    public function __construct(
        private readonly HttpClientInterface $client,
        #[AutowireLocator([new SubscribedService(type: Config::class)])]
        private readonly ContainerInterface $locator,
    )
    {
    }

    public function token(): string
    {
        if (null !== $this->token) {
            return $this->token;
        }

        $credentials = $this->credentials();
        $servername = $this->servername();
        $discovery = $this->client->request('GET', sprintf('https://%s/.well-known/openid-configuration', $servername))->toArray();
        $response = $this->client->request('POST', $discovery['token_endpoint'], ['body' => [
            'grant_type' => 'client_credentials',
            'client_id' => $credentials['clientId'],
            'client_secret' => $credentials['clientSecret'],
            'scope' => 'iserv:host:hosts:read iserv:idm:api-read',
        ]])->toArray();

        return $this->token = $response['access_token'];
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
            /** @var Config $config */
            $config = $this->locator->get(Config::class);
        } catch (ContainerExceptionInterface|NotFoundExceptionInterface $exception) {
            throw new \RuntimeException('Could not load IServ configuration.', previous: $exception);
        }

        return $config->getString('Servername');
    }
}
