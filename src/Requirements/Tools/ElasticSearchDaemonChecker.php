<?php

namespace Programgames\OroDev\Requirements\Tools;

use RuntimeException;
use Symfony\Component\Process\Process;

class ElasticSearchDaemonChecker implements DaemonCheckerInterface
{
    public function isDaemonRunning(): bool
    {
        $process = new Process(['brew', 'services', 'list']);
        $process->run();
        if (!$process->isSuccessful()) {
            return false;
        }
        preg_match('/.*elasticsearch.*/', $process->getOutput(), $matches);

        return BrewServiceParser::isServiceRunning(reset($matches));
    }

    public function getRunningPort(): int
    {
        throw new RuntimeException();
    }
}
