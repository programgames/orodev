<?php

namespace Programgames\OroDev\Requirements\Tools;

interface SatisfyingInterface
{
    public function satisfies(string $executable, string $constraints): bool;
}
