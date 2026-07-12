<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Configuration;

final class FileConfigurationRepository
{
    public function __construct(private readonly string $path = '/var/lib/iserv/unificonnector/config.json')
    {
    }

    public function find(): ?ConnectionConfiguration
    {
        if (!is_readable($this->path) || '' === trim((string) file_get_contents($this->path))) {
            return null;
        }

        /** @var array{url: string, username: string, password: string, fallbackGroup: string} $data */
        $data = json_decode((string) file_get_contents($this->path), true, 512, JSON_THROW_ON_ERROR);

        return new ConnectionConfiguration($data['url'], $data['username'], $data['password'], $data['fallbackGroup']);
    }

    public function store(ConnectionConfiguration $configuration): void
    {
        if (false === @touch($this->path) || false === file_put_contents($this->path, json_encode($configuration->toArray(), JSON_THROW_ON_ERROR))) {
            throw new \RuntimeException(sprintf('Cannot write %s.', $this->path));
        }
    }
}
