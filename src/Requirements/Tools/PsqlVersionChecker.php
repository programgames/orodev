<?php

namespace Programgames\OroDev\Requirements\Tools;

use Composer\Semver\Semver;
use RuntimeException;
use Symfony\Component\Process\Process;

class PsqlVersionChecker implements SatisfyingInterface
{
    public static function satisfies(string $executable, string $constraints): bool
    {
        $process = new Process([$executable, '-V']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('Failed to check "%s" program. %s, command not found', 'postgres', $process->getErrorOutput())
            );
        }
        preg_match('((?:\d.)+)', $process->getOutput(), $matches);
        return Semver::satisfies($matches[0], $constraints);
    }
}
