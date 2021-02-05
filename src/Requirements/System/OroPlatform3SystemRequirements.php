<?php

namespace Programgames\OroDev\Requirements\System;

class OroPlatform3SystemRequirements extends OroSystemRequirementCollection
{
    public const POSTGRES_VERSION = "~9.6";
    public const PSQL_VERSION = "~9.6";

    /**
     * OroPlatform3SystemRequirements constructor.
     * @param string $env
     * @noinspection PhpUnusedParameterInspection
     */
    public function __construct($env = 'prod')
    {
        $this->checkPostgresAndPSQL(self::POSTGRES_VERSION, self::PSQL_VERSION);
    }
}
