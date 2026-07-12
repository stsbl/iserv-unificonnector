<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\OAuth;

use IServ\Library\IdmApiClient\Authentication\Credentials;
use Psr\Http\Message\RequestInterface;

/** Supplies the OAuth bearer token through the IDM API client's credential hook. */
final readonly class OAuthCredentials implements Credentials
{
    public function __construct(private OAuthTokenProvider $tokenProvider)
    {
    }

    public function addToRequest(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Authorization', 'Bearer ' . $this->tokenProvider->token());
    }
}
