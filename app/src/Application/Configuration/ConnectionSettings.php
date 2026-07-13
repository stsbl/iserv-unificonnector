<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Application\Configuration;

final class ConnectionSettings
{
    public string $url = '';
    public string $authenticationMode = 'password';
    public string $username = '';
    public string $password = '';
    public string $apiKey = '';
    public string $fallbackGroup = '';
}
