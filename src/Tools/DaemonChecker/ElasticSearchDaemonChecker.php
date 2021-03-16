<?php

namespace Programgames\OroDev\Tools\DaemonChecker;

use Programgames\OroDev\Tools\BrewServiceParser;
use Programgames\OroDev\Tools\VersionChecker\ElasticSearchVersionChecker;
use RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class ElasticSearchDaemonChecker implements DaemonCheckerInterface
{
    /** @var ElasticSearchVersionChecker */
    private $elasticSearchVersionChecker;

    /**
     * ElasticSearchDaemonChecker constructor.
     * @param ElasticSearchVersionChecker $elasticSearchVersionChecker
     */
    public function __construct(ElasticSearchVersionChecker $elasticSearchVersionChecker)
    {
        $this->elasticSearchVersionChecker = $elasticSearchVersionChecker;
    }

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
        $version = $this->elasticSearchVersionChecker->getVersion();

        $config = Yaml::parse(file_get_contents(
            sprintf(
                '/usr/local/Cellar/elasticsearch-full/%s/libexec/config/elasticsearch.yml',
                $version
            )
        ));

        if (array_key_exists('http.port', $config)) {
            return $config['http.port'];
        }

        return 9200;
    }

    public function getPid(): int
    {
        $process = new Process(['ps', 'aux']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('Failed to use "%s" program. %s, command not found', 'ps | aux', $process->getErrorOutput())
            );
        }

        preg_match('/.*java.*elastic.*/', $process->getOutput(), $matches);

        return (int)$matches[0];
    }
}
