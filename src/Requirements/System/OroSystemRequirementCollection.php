<?php

namespace Programgames\OroDev\Requirements\System;

use Programgames\OroDev\Tools\DaemonChecker\MailcatcherDaemonChecker;
use Programgames\OroDev\Tools\DaemonChecker\PostgresDaemonChecker;
use Programgames\OroDev\Tools\ExecutableFinder\MailcatcherExecutableFinder;
use Programgames\OroDev\Tools\ExecutableFinder\PostgresExecutableFinder;
use Programgames\OroDev\Tools\ExecutableFinder\PsqlExecutableFinder;
use Programgames\OroDev\Tools\VersionChecker\PostgresVersionChecker;
use Programgames\OroDev\Tools\VersionChecker\PsqlVersionChecker;
use Symfony\Requirements\RequirementCollection;

class OroSystemRequirementCollection extends RequirementCollection implements PostgresAndPSQLCheckerInterface
{
    /** @var MailcatcherDaemonChecker */
    private $mailcatcherDaemonChecker;

    /** @var PostgresDaemonChecker */
    private $postgresDaemonChecker;

    /** @var MailcatcherExecutableFinder */
    private $mailcatcherExecutableFinder;

    /** @var PostgresExecutableFinder */
    private $postgresExecutableFinder;

    /** @var PsqlExecutableFinder */
    private $psqlExecutableFinder;

    /** @var PostgresVersionChecker */
    private $postgresVersionChecker;

    /** @var PsqlVersionChecker */
    private $psqlVersionChecker;

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
        $this->mailcatcherDaemonChecker = $mailcatcherDaemonChecker;
        $this->postgresDaemonChecker = $postgresDaemonChecker;
        $this->mailcatcherExecutableFinder = $mailcatcherExecutableFinder;
        $this->postgresExecutableFinder = $postgresExecutableFinder;
        $this->psqlExecutableFinder = $psqlExecutableFinder;
        $this->postgresVersionChecker = $postgresVersionChecker;
        $this->psqlVersionChecker = $psqlVersionChecker;
    }

    public function checkPostgresAndPSQL(string $postgresVersion, string $psqlVersion): void
    {
        $postgresFinder = new PostgresExecutableFinder();
        $postgresExecutable = $postgresFinder->findExecutable();
        $postgresExist = null !== $postgresExecutable;
        $this->addSystemRequirement(
            $postgresExist,
            sprintf('Postgres server must be installed'),
            $postgresExist ? 'Postgres is installed' : 'Postgres must be installed'
        );
        $postgresVersionChecker = new PostgresVersionChecker();
        $this->addSystemRequirement(
            $postgresVersionChecker->satisfies($postgresExecutable, $postgresVersion),
            sprintf('Postgres "%s" version must be installed.', $postgresVersion),
            sprintf('Upgrade <strong>Postgres</strong> to "%s" version.', $postgresVersion)
        );

        $psqlFinder = new PsqlExecutableFinder();
        $psqlExecutable = $psqlFinder->findExecutable();
        $psqlExist = null !== $psqlExecutable;
        $this->addSystemRequirement(
            $psqlExist,
            sprintf('Psql server must be installed'),
            $psqlExist ? 'Psql is installed' : 'Psql must be installed'
        );


        $this->addSystemRequirement(
            $this->psqlVersionChecker->satisfies($psqlExecutable, $psqlVersion),
            sprintf('Psql "%s" version must be installed.', $psqlVersion),
            sprintf('Upgrade <strong>Psql</strong> to "%s" version.', $psqlVersion)
        );

        $this->addSystemRequirement(
            $this->postgresDaemonChecker->isDaemonRunning(),
            'Postgres Daemon must be running',
            'Run the postgres daemon'
        );

        $mailcatcherExecutable = $this->mailcatcherExecutableFinder->findExecutable();
        $mailcatcherExist = null !== $mailcatcherExecutable;
        $this->addSystemRequirement(
            $mailcatcherExist,
            sprintf('Mailcatcher must be installed'),
            $mailcatcherExist ? 'Mailcatcher is installed' : 'Mailcatcher must be installed'
        );

        $this->addSystemRequirement(
            $this->mailcatcherDaemonChecker->isDaemonRunning(),
            sprintf('Mailcatcher daemon must be running'),
            sprintf('Run the Mailcatcher daemon')
        );
    }
    
    /**
     * Adds an OroDev specific requirement.
     *
     * @param Boolean $fulfilled Whether the requirement is fulfilled
     * @param string $testMessage The message for testing the requirement
     * @param string $helpHtml The help text formatted in HTML for resolving the problem
     * @param string|null $helpText The help text (when null, it will be inferred from $helpHtml, i.e. stripped from HTML tags)
     */
    public function addSystemRequirement(bool $fulfilled, string $testMessage, string $helpHtml, $helpText = null):void
    {
        $this->add(new OroSystemRequirement($fulfilled, $testMessage, $helpHtml, $helpText, false));
    }
}
