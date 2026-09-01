<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Host;

use IServ\UnifiConnector\Host\HostApiRepository;
use IServ\UnifiConnector\OAuth\AccessTokenProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(HostApiRepository::class)]
final class HostApiRepositoryTest extends TestCase
{
    public function testFetchesHostsThroughTheServerModulesApiWithBearerToken(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::once())->method('toArray')->willReturn([[
            'id' => '1a995a83-b843-4c9f-b2e7-6c9e3b72f005',
            'name' => 'printer',
            'ip' => '10.0.0.42',
            'mac' => '00:11:22:33:44:55',
            'ownerId' => 'e588f8be-e0c1-49cd-aa76-4ca563d8c187',
        ]]);
        $tokens = $this->createMock(AccessTokenProvider::class);
        $tokens->expects(self::once())->method('token')->willReturn('access-token');
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects(self::once())->method('request')->with(
            'GET',
            'http://localhost:982/iserv/host/api/v0/hosts',
            ['query' => ['limit' => 1000], 'auth_bearer' => 'access-token'],
        )->willReturn($response);

        $hosts = iterator_to_array((new HostApiRepository($client, $tokens))->findAll());

        self::assertCount(1, $hosts);
        self::assertSame('printer', $hosts[0]->getName());
        self::assertSame('00:11:22:33:44:55', $hosts[0]->getMac());
        self::assertSame('e588f8be-e0c1-49cd-aa76-4ca563d8c187', $hosts[0]->getOwnerUuid());
    }
}
