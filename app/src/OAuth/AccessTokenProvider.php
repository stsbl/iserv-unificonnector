<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\OAuth;

/** Provides an OAuth access token for IServ service APIs. */
interface AccessTokenProvider
{
    public function token(): string;
}
