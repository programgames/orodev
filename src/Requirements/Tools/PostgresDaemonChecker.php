<?php

namespace Programgames\OroDev\Requirements\Tools;

use Symfony\Component\Process\Process;

class PostgresDaemonChecker implements DaemonCheckerInterface
{
    public function isDaemonRunning(): bool
    {
        $process = new Process(['pg_isready']);
        $process->run();
        if (!$process->isSuccessful()) {
            return false;
        }
        $postgresOutput = $process->getOutput();
        return preg_match('/accepting connections/', $postgresOutput, $matches);
    }

    public function getRunningPort(): int
    {
        $process = new Process(['pg_isready']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                sprintf('Failed to check "%s" daemon. %s, command not found', 'postgres', $process->getErrorOutput())
            );
        }
        $postgresOutput = $process->getOutput();
        preg_match('/[0-9]+/', $postgresOutput, $version);
        return $version[0];
    }
}
