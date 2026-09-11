<?php

declare(strict_types=1);

namespace Stsbl\IServ\UnifiConnector\Unifi;

use UniFi_API\Client;

interface PasswordClientFactory
{
    public function create(string $username, string $password, string $url): Client;
}
