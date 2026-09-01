<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Tests\Unit\Synchronisation;

use IServ\UnifiConnector\Synchronisation\SyncRunner;
use IServ\Library\Shell\ExecutionResult;
use IServ\Library\Shell\Shell;
use IServ\Library\Shell\Stream\OutputStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SyncRunner::class)]
final class SyncRunnerTest extends TestCase
{
    public function testStreamsOutputAndReportsFailedCommands(): void
    {
        $shell = $this->createMock(Shell::class);
        $shell->expects(self::once())->method('execute')->willReturnCallback(static function (string $command, array $args, string $stdin, ?array $env, ?OutputStream $stream): ExecutionResult {
            self::assertSame('/usr/bin/iservunificonnector-console', $command);
            self::assertSame(['unificonnector:sync', '--no-interaction', '--verbose'], $args);
            self::assertSame('', $stdin);
            self::assertNull($env);
            self::assertInstanceOf(OutputStream::class, $stream);
            $stream->receiveStdout("stdout\n");
            $stream->receiveStderr("stderr\n");

            return new ExecutionResult(7, null, null);
        });
        $output = [];

        (new SyncRunner($shell))->stream(static function (string $line) use (&$output): void {
            $output[] = $line;
        });

        self::assertSame(["stdout\n", "stderr\n", "Synchronization failed with exit code 7.\n"], $output);
    }
}
