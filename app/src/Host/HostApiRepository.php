<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Host;

use IServ\UnifiConnector\OAuth\OAuthTokenProvider;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/** Uses the Host module API because IDM's host projection can be stale. */
final readonly class HostApiRepository implements HostRepository
{
    public function __construct(private HttpClientInterface $client, private OAuthTokenProvider $tokenProvider)
    {
    }

    public function findAll(): iterable
    {
        /** @var list<array{id: string, name: string, ip: string, mac?: string|null, ownerId?: string|null}> $hosts */
        $hosts = $this->client->request('GET', 'http://localhost:982/api/v0/hosts', ['query' => ['limit' => 1000], 'auth_bearer' => $this->tokenProvider->token()])->toArray();

        foreach ($hosts as $host) {
            yield Host::fromDatabaseRow([
                'uuid' => $host['id'],
                'name' => $host['name'],
                'ip' => $host['ip'],
                'mac' => $host['mac'] ?? null,
                'owner_uuid' => $host['ownerId'] ?? null,
            ]);
        }
    }
}
