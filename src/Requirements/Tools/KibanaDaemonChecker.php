<?php

namespace Programgames\OroDev\Requirements\Tools;

use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class KibanaDaemonChecker implements DaemonCheckerInterface
{
    public static function isDaemonRunning(): bool
    {
        $process = new Process(['brew', 'services', 'list']);
        $process->run();
        if (!$process->isSuccessful()) {
            return false;
        }
        preg_match('/.*kibana.*/', $process->getOutput(), $matches);

        return BrewServiceParser::isServiceRunning(reset($matches));
    }

    public static function getRunningPort(): int
    {
        //TODO : rendre generique
        $config = Yaml::parse(file_get_contents('/usr/local/etc/kibana/kibana.yml'));

        if (array_key_exists('http:port', $config)) {
            return $config['http:port'];
        }

        return 5601;
    }

    public static function getPid(): int
    {
        //TODO implement
        return 0;
    }
}
