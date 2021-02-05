<?php

namespace Programgames\OroDev\Requirements\System;

interface PostgresAndPSQLCheckerInterface
{
    public function checkPostgresAndPSQL(string $postgresVersion, string $psqlVersion);
}
