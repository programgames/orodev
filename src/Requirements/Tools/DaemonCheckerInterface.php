<?php

namespace Programgames\OroDev\Requirements\Tools;

interface DaemonCheckerInterface
{
    public static function isDaemonRunning(): bool;

    public static function getRunningPort(): int;

    public static function getPid(): int;
}
