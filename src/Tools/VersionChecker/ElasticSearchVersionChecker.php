<?php

namespace Programgames\OroDev\Tools\VersionChecker;

use Composer\Semver\Semver;
use Programgames\OroDev\Tools\ExecutableFinder\ElasticSearchExecutableFinder;
use RuntimeException;
use Symfony\Component\Process\Process;

class ElasticSearchVersionChecker implements VersionCheckerInterface
{
    /** @var ElasticSearchExecutableFinder */
    private $elasticSearchExecutableFinder;

    /**
     * ElasticSearchVersionChecker constructor.
     * @param ElasticSearchExecutableFinder $elasticSearchExecutableFinder
     */
    public function __construct(ElasticSearchExecutableFinder $elasticSearchExecutableFinder)
    {
        $this->elasticSearchExecutableFinder = $elasticSearchExecutableFinder;
    }

    public function satisfies(string $executable, string $constraints): bool
    {
        $process = new Process([$executable, '--version']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('Failed to check "%s" program. %s, command not found', $executable, $process->getErrorOutput())
            );
        }
        $output = $process->getOutput();
        preg_match(
            '/(\d+)\.(\d+)\.(\d+)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+[0-9A-Za-z-]+)?/',
            $output,
            $matches
        );

        return Semver::satisfies($matches[0], $constraints);
    }

    public function getVersion(): string
    {
        $executable = $this->elasticSearchExecutableFinder->findExecutable();
        $process = new Process([$executable, '--version']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('Failed to check "%s" program. %s, command not found', $executable, $process->getErrorOutput())
            );
        }
        $output = $process->getOutput();
        preg_match(
            '/(\d+)\.(\d+)\.(\d+)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+[0-9A-Za-z-]+)?/',
            $output,
            $matches
        );

        return reset($matches);
    }


}
