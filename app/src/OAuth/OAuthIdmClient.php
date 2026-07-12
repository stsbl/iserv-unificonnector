<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\OAuth;

use IServ\Library\IdmApiClient\Hydrator\HydratorInterface;
use IServ\Library\IdmApiClient\IdmClientInterface;
use Psr\Http\Message\StreamInterface;

final readonly class OAuthIdmClient implements IdmClientInterface
{
    public function __construct(private IdmClientInterface $client, private OAuthTokenProvider $tokenProvider)
    {
    }

    public function performRequest(string $method, string $url, HydratorInterface $hydrator, array $headers = [], StreamInterface $body = null): mixed
    {
        return $this->client->performRequest($method, $url, $hydrator, ['Authorization' => 'Bearer ' . $this->tokenProvider->token()] + $headers, $body);
    }
}
