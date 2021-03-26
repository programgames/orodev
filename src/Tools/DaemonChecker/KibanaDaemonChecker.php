<?php

namespace Programgames\OroDev\Tools\DaemonChecker;

use Programgames\OroDev\Exception\DaemonNotRunning;
use Programgames\OroDev\Tools\BrewServiceParser;
use RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class KibanaDaemonChecker implements DaemonCheckerInterface
{
    public function isDaemonRunning(): bool
    {
        $process = new Process(['brew', 'services', 'list']);
        $process->run();
        if (!$process->isSuccessful()) {
            return false;
        }
        preg_match('/.*kibana.*/', $process->getOutput(), $matches);

        return BrewServiceParser::isServiceRunning(reset($matches));
    }

    public function getRunningPort(): int
    {
        //TODO : rendre generique
        $config = Yaml::parse(file_get_contents('/usr/local/etc/kibana/kibana.yml'));

        if (array_key_exists('http:port', $config)) {
            return $config['http:port'];
        }

        return 5601;
    }

    public function getPid(): int
    {
        $process = new Process(['ps', 'aux']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('Failed to check "%s" PID. %s, command not found', 'kibana', $process->getErrorOutput())
            );
        }
        $lsofOutput = $process->getOutput();
        preg_match('/.*kibana.*/', $lsofOutput, $matches);
        $kibanaProcess = preg_replace('/\s+/', ' ', reset($matches));

        if (empty($kibanaProcess)) {
            throw new DaemonNotRunning('Mailcatcher is not running');
        }

        $pieces = explode(' ', $kibanaProcess);

        return $pieces[1];
    }
}
