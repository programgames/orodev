<?php

namespace Programgames\OroDev\Tools\VersionChecker;

use Composer\Semver\Semver;
use RuntimeException;
use Symfony\Component\Process\Process;

class RabbitMqVersionChecker implements SatisfyingInterface
{
    public function satisfies(string $executable, string $constraints): bool
    {
        $process = new Process([$executable, 'status']);
        $process->run();
        if (!$process->isSuccessful()) {
            return false;
        }
        $output = $process->getOutput();
        preg_match(
            '/.*(RabbitMQ version: ).*(\d+)\.(\d+)\.(\d+)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+[0-9A-Za-z-]+)?/',
            $output,
            $matches
        );
        if ($matches === null) {
            throw new RuntimeException(
                sprintf('Failed to check "%s" version. %s, command not found', $executable, $output)
            );
        }
        $version = str_replace($matches[1], '', $matches[0]);
        return Semver::satisfies($version, $constraints);
    }
}
