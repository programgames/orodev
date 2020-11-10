<?php

namespace Programgames\OroDev\Requirements\Tools;

interface DaemonCheckerInterface
{
    public function isDaemonRunning(): bool;

    public function getRunningPort(): int;
}
