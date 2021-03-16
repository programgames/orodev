<?php

namespace Programgames\OroDev\Tools\ExecutableFinder;

use Symfony\Component\Process\ExecutableFinder;

class ElasticSearchExecutableFinder implements ExecutableFinderInterface
{
    public const EXECUTABLE = 'elasticsearch';

    public function findExecutable(): ?string
    {
        $executableFinder = new ExecutableFinder();

        $executable = $executableFinder->find(self::EXECUTABLE);
        return $executable ?? null;
    }
}
