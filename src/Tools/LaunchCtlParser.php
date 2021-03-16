<?php

namespace Programgames\OroDev\Tools;

use Programgames\OroDev\Exception\DaemonNotRunning;
use Symfony\Component\Process\Process;

class LaunchCtlParser
{
    public function getService(string $serviceName): string
    {
        $process = new Process(['launchctl']);
        $process->run();
        if (!$process->isSuccessful()) {
            return false;
        }
        $postgresOutput = $process->getOutput();
        preg_match('/.*.' . $serviceName.'.*/', $postgresOutput, $matches);

        if (empty($matches)) {
            throw new DaemonNotRunning('daemon not running');
        }
        return $matches[0];
    }


    public function getPid(string $serviceName): string
    {
        $service = $this->getService($serviceName);

        $parts = preg_split('/\s+/', $service);

        return $parts[1];
    }
}
