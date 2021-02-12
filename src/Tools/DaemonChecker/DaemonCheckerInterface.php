<?php

namespace Programgames\OroDev\Tools\DaemonChecker;

interface DaemonCheckerInterface
{
    public function isDaemonRunning(): bool;

    public function getRunningPort(): int;

    public function getPid(): int;
}
