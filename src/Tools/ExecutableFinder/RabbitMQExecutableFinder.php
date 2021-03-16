<?php

namespace Programgames\OroDev\Tools\ExecutableFinder;

use Symfony\Component\Process\ExecutableFinder;

class RabbitMQExecutableFinder implements ExecutableFinderInterface
{
    public const EXECUTABLE = 'rabbitmq-server';

    public function findExecutable(): ?string
    {
        $executableFinder = new ExecutableFinder();

        $executable = $executableFinder->find(self::EXECUTABLE);
        return $executable ?? null;
    }
}
