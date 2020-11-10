<?php

namespace Programgames\OroDev\Requirements\Tools;

use Symfony\Component\Process\ExecutableFinder;

class PostgresExecutableFinder implements ExecutableFinderInterface
{
    public const EXECUTABLE = "postgres";
    /**
     * @return null|string
     */
    public function findExecutable(): ?string
    {
        $executableFinder = new ExecutableFinder();

        $executable = $executableFinder->find(self::EXECUTABLE);
        if (null !== $executable) {
            return $executable;
        }

        return null;
    }
}
