<?php

declare(strict_types=1);

namespace Stsbl\IServ\UnifiConnector\Synchronisation;

interface SyncRunnerInterface
{
    /** @param callable(string): void $write */
    public function stream(callable $write): void;
}
