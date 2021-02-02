<?php

namespace Programgames\OroDev\Requirements\Tools;

use Symfony\Component\Process\ExecutableFinder;

class MailcatcherExecutableFinder implements ExecutableFinderInterface
{
    const EXECUTABLE = 'mailcatcher';

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