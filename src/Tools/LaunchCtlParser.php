<?php

namespace Programgames\OroDev\Tools;

class LaunchCtlParser
{
    public static function getPid(string $string): string
    {
        $parts = preg_split('/\s+/', $string);

        return $parts[1];
    }
}
