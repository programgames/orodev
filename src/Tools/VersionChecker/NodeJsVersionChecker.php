<?php

namespace Programgames\OroDev\Tools\VersionChecker;

use Composer\Semver\Semver;
use RuntimeException;
use Symfony\Component\Process\Process;

class NodeJsVersionChecker implements SatisfyingInterface
{
    /**
     * @param string $executable
     * @param string $constraints
     * @return bool
     */
    public function satisfies(string $executable, string $constraints): bool
    {
        $process = new Process([$executable, '-v']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('Failed to check "%s" version. %s', $executable, $process->getErrorOutput())
            );
        }
        $version = $process->getOutput();

        return Semver::satisfies($version, $constraints);
    }
}
