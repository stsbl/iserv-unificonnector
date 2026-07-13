<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Unifi;

use UniFi_API\Client;

/**
 * UniFi OS Network client authenticated with a local controller API key.
 */
final class ApiKeyClient extends Client
{
    public function __construct(string $baseUrl, string $apiKey)
    {
        parent::__construct('', '', $baseUrl, 'default', '', true);

        $this->is_unifi_os = true;
        $this->is_logged_in = true;
        $this->curl_headers[] = 'X-API-KEY: ' . $apiKey;
    }

    public function login(): bool
    {
        return true;
    }
}
