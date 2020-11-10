<?php

namespace Programgames\OroDev\Requirements\Tools;

use RuntimeException;
use Symfony\Component\Process\Process;

class ElasticSearchDaemonChecker implements DaemonCheckerInterface
{
    public function isDaemonRunning(): bool
    {
        $process = new Process(['launchctl', 'list']);
        $process->run();
        if (!$process->isSuccessful()) {
            return false;
        }
        $running = preg_match('/.*elasticsearch.*/', $process->getOutput(), $matches);
        if (!$running) {
            return false;
        }
        if (LaunchCtlParser::getPid($matches[0])) {
            return false;
        }
        return true;
    }

    public function getRunningPort(): int
    {
        throw new RuntimeException();
    }
}
