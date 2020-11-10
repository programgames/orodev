<?php

namespace Programgames\OroDev\Requirements\Tools;

use Composer\Semver\Semver;
use RuntimeException;
use Symfony\Component\Process\Process;

class ElasticSearchVersionChecker implements SatisfyingInterface
{
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
        preg_match('/([0-9]+)\.([0-9]+)\.([0-9]+)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+[0-9A-Za-z-]+)?/', $output, $matches);

        return Semver::satisfies($matches[0], $constraints);
    }
}
