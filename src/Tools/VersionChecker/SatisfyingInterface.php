<?php

namespace Programgames\OroDev\Tools\VersionChecker;

interface SatisfyingInterface
{
    public function satisfies(string $executable, string $constraints): bool;
}
