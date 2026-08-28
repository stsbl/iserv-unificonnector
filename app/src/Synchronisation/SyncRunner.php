<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Synchronisation;

use Symfony\Component\Process\Process;

final class SyncRunner
{
    /** @param callable(string): void $write */
    public function stream(callable $write): void
    {
        $process = $this->createProcess();
        $process->run(static function (string $type, string $buffer) use ($write): void {
            $write($buffer);
        });

        if (!$process->isSuccessful()) {
            $write(sprintf("Synchronization failed with exit code %d.\n", $process->getExitCode() ?? -1));
        }
    }

    private function createProcess(): Process
    {
        return new Process(['/usr/bin/iservunificonnector-console', 'unificonnector:sync', '--no-interaction', '--verbose']);
    }
}
