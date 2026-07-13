<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Configuration;

final readonly class ConnectionConfiguration
{
    public function __construct(
        public string $url,
        public string $username,
        public string $password,
        public string $fallbackGroup,
        public string $authenticationMode = 'password',
        public string $apiKey = '',
    ) {
    }

    /** @return array{url: string, username: string, password: string, fallbackGroup: string, authenticationMode: string, apiKey: string} */
    public function toArray(): array
    {
        return ['url' => $this->url, 'username' => $this->username, 'password' => $this->password, 'fallbackGroup' => $this->fallbackGroup, 'authenticationMode' => $this->authenticationMode, 'apiKey' => $this->apiKey];
    }
}
