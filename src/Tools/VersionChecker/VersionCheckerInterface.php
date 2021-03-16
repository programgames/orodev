<?php

namespace Programgames\OroDev\Tools\VersionChecker;

interface VersionCheckerInterface extends SatisfyingInterface
{
    public function getVersion(): string;
}
