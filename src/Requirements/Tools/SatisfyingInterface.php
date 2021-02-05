<?php

namespace Programgames\OroDev\Requirements\Tools;

interface SatisfyingInterface
{
    public static function satisfies(string $executable, string $constraints): bool;
}
