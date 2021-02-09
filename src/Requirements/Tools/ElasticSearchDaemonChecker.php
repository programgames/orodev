<?php

namespace Programgames\OroDev\Requirements\Tools;

use RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class ElasticSearchDaemonChecker implements DaemonCheckerInterface
{
    public static function isDaemonRunning(): bool
    {
        $process = new Process(['brew', 'services', 'list']);
        $process->run();
        if (!$process->isSuccessful()) {
            return false;
        }
        preg_match('/.*elasticsearch.*/', $process->getOutput(), $matches);

        return BrewServiceParser::isServiceRunning(reset($matches));
    }

    public static function getRunningPort(): int
    {
        //TODO : rendre generique
        $config = Yaml::parse(file_get_contents('/usr/local/Cellar/elasticsearch-full/7.10.1/libexec/config/elasticsearch.yml'));

        if (array_key_exists('http:port', $config)) {
            return $config['http:port'];
        }

        return 9200;
    }

    public static function getPid(): int
    {
        throw new RuntimeException('Not implemented');
    }
}
