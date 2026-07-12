<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Synchronisation;

use Symfony\Component\Process\Process;

final class SyncRunner
{
    public function run(): void
    {
        $process = new Process(['/usr/bin/iservunificonnector-console', 'unificonnector:sync', '--no-interaction']);
        $process->mustRun();
    }
}
