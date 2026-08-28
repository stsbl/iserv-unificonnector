<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Unifi;

final readonly class UnifiUrl
{
    private function __construct(public string $value)
    {
    }

    public static function fromString(string $url): self
    {
        $url = rtrim($url, '/');

        return new self('https://' . (preg_replace('#^https?://#i', '', $url) ?? $url));
    }
}
