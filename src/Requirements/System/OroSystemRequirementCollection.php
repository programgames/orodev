<?php

namespace Programgames\OroDev\Requirements\System;

use Programgames\OroDev\Requirements\Tools\MailcatcherDaemonChecker;
use Programgames\OroDev\Requirements\Tools\MailcatcherExecutableFinder;
use Programgames\OroDev\Requirements\Tools\PostgresDaemonChecker;
use Programgames\OroDev\Requirements\Tools\PostgresExecutableFinder;
use Programgames\OroDev\Requirements\Tools\PostgresVersionChecker;
use Programgames\OroDev\Requirements\Tools\PsqlExecutableFinder;
use Programgames\OroDev\Requirements\Tools\PsqlVersionChecker;
use Symfony\Requirements\RequirementCollection;

class OroSystemRequirementCollection extends RequirementCollection implements PostgresAndPSQLCheckerInterface
{
    public function checkPostgresAndPSQL(string $postgresVersion, string $psqlVersion)
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

        $psqlVersionChecker = new PsqlVersionChecker();
        $this->addSystemRequirement(
            $psqlVersionChecker->satisfies($psqlExecutable, $psqlVersion),
            sprintf('Psql "%s" version must be installed.', $psqlVersion),
            sprintf('Upgrade <strong>Psql</strong> to "%s" version.', $psqlVersion)
        );

        $this->addSystemRequirement(
            PostgresDaemonChecker::isDaemonRunning(),
            'Postgres Daemon must be running',
            'Run the postgres daemon'
        );

        $mailcatcherFinder = new MailcatcherExecutableFinder();
        $mailcatcherExecutable = $mailcatcherFinder->findExecutable();
        $mailcatcherExist = null !== $mailcatcherExecutable;
        $this->addSystemRequirement(
            $mailcatcherExist,
            sprintf('Mailcatcher must be installed'),
            $mailcatcherExist ? 'Mailcatcher is installed' : 'Mailcatcher must be installed'
        );

        $this->addSystemRequirement(
            MailcatcherDaemonChecker::isDaemonRunning(),
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
    public function addSystemRequirement(bool $fulfilled, string $testMessage, string $helpHtml, $helpText = null)
    {
        $this->add(new OroSystemRequirement($fulfilled, $testMessage, $helpHtml, $helpText, false));
    }
}
