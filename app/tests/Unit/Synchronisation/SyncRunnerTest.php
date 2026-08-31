<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Synchronisation;

use IServ\UnifiConnector\Synchronisation\SyncRunner;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[CoversClass(SyncRunner::class)]
final class SyncRunnerTest extends TestCase
{
    public function testWebSynchronizationHasNoFixedTimeout(): void
    {
        $method = new \ReflectionMethod(SyncRunner::class, 'createProcess');
        /** @var Process $process */
        $process = $method->invoke(new SyncRunner());

        self::assertNull($process->getTimeout());
    }
}
