<?php

namespace Programgames\OroDev\Tools\ExecutableFinder;

use Symfony\Component\Process\ExecutableFinder;

class PsqlExecutableFinder implements ExecutableFinderInterface
{
    public const EXECUTABLE = "psql";
    /**
     * @return null|string
     */
    public function findExecutable(): ?string
    {
        $executableFinder = new ExecutableFinder();

        $executable = $executableFinder->find(self::EXECUTABLE);
        return $executable ?? null;
    }
}
