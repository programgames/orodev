<?php

namespace Programgames\OroDev\Requirements\Tools;

use Composer\Semver\Semver;
use RuntimeException;
use Symfony\Component\Process\Process;

class NodeJsVersionChecker implements SatisfyingInterface
{
    /**
     * @param string $nodeJsExecutable
     * @param string $constraints
     * @return bool
     */
    public function satisfies(string $nodeJsExecutable, string $constraints): bool
    {
        $process = new Process([$nodeJsExecutable, '-v']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('Failed to check "%s" version. %s', $nodeJsExecutable, $process->getErrorOutput())
            );
        }
        $version = $process->getOutput();

        return Semver::satisfies($version, $constraints);
    }
}
