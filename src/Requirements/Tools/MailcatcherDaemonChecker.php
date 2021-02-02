<?php

namespace Programgames\OroDev\Requirements\Tools;

use RuntimeException;
use Symfony\Component\Process\Process;

class MailcatcherDaemonChecker implements DaemonCheckerInterface
{
    public function isDaemonRunning(): bool
    {
        $process = new Process(['ps', 'aux']);
        $process->run();
        if (!$process->isSuccessful()) {
            return false;
        }
        $running = preg_match('/.*mailcatcher.*/', $process->getOutput(), $matches);
        if (!$running) {
            return false;
        }
        return true;
    }

    public function getRunningPort(): int
    {
        $process = new Process(['lsof', '-Pni4']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('Failed to check "%s" daemon. %s, command not found', 'mailcatcher', $process->getErrorOutput())
            );
        }
        $lsofOutput = $process->getOutput();
        preg_match('/.*ruby.*/', $lsofOutput, $matches);

        throw new RuntimeException('Implementation not finished');

        return 1025;
    }
}
