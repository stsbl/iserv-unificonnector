<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Unifi;

use UniFi_API\Client;

final class NativePasswordClientFactory implements PasswordClientFactory
{
    public function create(string $username, string $password, string $url): Client
    {
        return new Client($username, $password, $url, '', '', true);
    }
}
