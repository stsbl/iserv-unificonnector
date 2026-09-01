<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Synchronisation;

use IServ\Library\Shell\Shell;
use IServ\Library\Shell\Stream\CallbackStream;

final class SyncRunner implements SyncRunnerInterface
{
    public function __construct(private readonly Shell $shell)
    {
    }

    /** @param callable(string): void $write */
    public function stream(callable $write): void
    {
        $result = $this->shell->execute(
            '/usr/bin/iservunificonnector-console',
            ['unificonnector:sync', '--no-interaction', '--verbose'],
            stream: new CallbackStream($write, $write),
        );

        if (!$result->isSuccessful()) {
            $write(sprintf("Synchronization failed with exit code %d.\n", $result->getExitCode()));
        }
    }
}
