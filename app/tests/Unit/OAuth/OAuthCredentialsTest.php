<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\OAuth;

use IServ\UnifiConnector\OAuth\AccessTokenProvider;
use IServ\UnifiConnector\OAuth\OAuthCredentials;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OAuthCredentials::class)]
final class OAuthCredentialsTest extends TestCase
{
    public function testAddsBearerTokenWithoutChangingTheOriginalRequest(): void
    {
        $tokens = $this->createMock(AccessTokenProvider::class);
        $tokens->expects(self::once())->method('token')->willReturn('access-token');
        $request = new Request('GET', 'https://iserv.test/api');

        $authenticated = (new OAuthCredentials($tokens))->addToRequest($request);

        self::assertSame('Bearer access-token', $authenticated->getHeaderLine('Authorization'));
        self::assertFalse($request->hasHeader('Authorization'));
    }
}
