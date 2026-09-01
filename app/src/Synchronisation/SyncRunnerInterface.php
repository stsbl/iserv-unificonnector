<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Synchronisation;

interface SyncRunnerInterface
{
    /** @param callable(string): void $write */
    public function stream(callable $write): void;
}
