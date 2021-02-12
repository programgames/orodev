<?php

namespace Programgames\OroDev\Requirements\System;

use Programgames\OroDev\Tools\DaemonChecker\MailcatcherDaemonChecker;
use Programgames\OroDev\Tools\DaemonChecker\PostgresDaemonChecker;
use Programgames\OroDev\Tools\ExecutableFinder\MailcatcherExecutableFinder;
use Programgames\OroDev\Tools\ExecutableFinder\PostgresExecutableFinder;
use Programgames\OroDev\Tools\ExecutableFinder\PsqlExecutableFinder;
use Programgames\OroDev\Tools\VersionChecker\PostgresVersionChecker;
use Programgames\OroDev\Tools\VersionChecker\PsqlVersionChecker;

class OroPlatform3SystemRequirements extends OroSystemRequirementCollection
{
    public const POSTGRES_VERSION = "~9.6";
    public const PSQL_VERSION = "~9.6";

    /**
     * OroSystemRequirementCollection constructor.
     * @param MailcatcherDaemonChecker $mailcatcherDaemonChecker
     * @param PostgresDaemonChecker $postgresDaemonChecker
     * @param MailcatcherExecutableFinder $mailcatcherExecutableFinder
     * @param PostgresExecutableFinder $postgresExecutableFinder
     * @param PsqlExecutableFinder $psqlExecutableFinder
     * @param PostgresVersionChecker $postgresVersionChecker
     * @param PsqlVersionChecker $psqlVersionChecker
     */
    public function __construct(
        MailcatcherDaemonChecker $mailcatcherDaemonChecker,
        PostgresDaemonChecker $postgresDaemonChecker,
        MailcatcherExecutableFinder $mailcatcherExecutableFinder,
        PostgresExecutableFinder $postgresExecutableFinder,
        PsqlExecutableFinder $psqlExecutableFinder,
        PostgresVersionChecker $postgresVersionChecker,
        PsqlVersionChecker $psqlVersionChecker
    ) {
        {
            parent::__construct(
                $mailcatcherDaemonChecker,
                $postgresDaemonChecker,
                $mailcatcherExecutableFinder,
                $postgresExecutableFinder,
                $psqlExecutableFinder,
                $postgresVersionChecker,
                $psqlVersionChecker
            );

            $this->checkPostgresAndPSQL(self::POSTGRES_VERSION, self::PSQL_VERSION);
        }
    }
}
