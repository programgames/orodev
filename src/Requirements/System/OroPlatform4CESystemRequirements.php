<?php

namespace Programgames\OroDev\Requirements\System;

use Programgames\OroDev\Requirements\Tools\ElasticSearchDaemonChecker;
use Programgames\OroDev\Requirements\Tools\ElasticSearchExecutableFinder;
use Programgames\OroDev\Requirements\Tools\MailcatcherExecutableFinder;
use Programgames\OroDev\Requirements\Tools\MailcatcherDaemonChecker;
use Programgames\OroDev\Requirements\Tools\PostgresDaemonChecker;
use Programgames\OroDev\Requirements\Tools\PostgresExecutableFinder;
use Programgames\OroDev\Requirements\Tools\ElasticSearchVersionChecker;
use Programgames\OroDev\Requirements\Tools\PostgresVersionChecker;
use Programgames\OroDev\Requirements\Tools\PsqlExecutableFinder;
use Programgames\OroDev\Requirements\Tools\PsqlVersionChecker;
use Programgames\OroDev\Requirements\Tools\RabbitMqDaemonChecker;
use Programgames\OroDev\Requirements\Tools\RabbitMQExecutableFinder;
use Programgames\OroDev\Requirements\Tools\RabbitMqVersionChecker;
use Symfony\Requirements\RequirementCollection;

class OroPlatform4CESystemRequirements extends RequirementCollection
{
    public const POSTGRES_VERSION = "~9.6";
    public const PSQL_VERSION = "~9.6";

    /**
     * OroDevRequirements constructor.
     */
    public function __construct($env = 'prod')
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
            $postgresVersionChecker->satisfies($postgresExecutable, self::POSTGRES_VERSION),
            sprintf('Postgres "%s" version must be installed.', self::POSTGRES_VERSION),
            sprintf('Upgrade <strong>Postgres</strong> to "%s" version.', self::POSTGRES_VERSION)
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
            $psqlVersionChecker->satisfies($psqlExecutable, self::PSQL_VERSION),
            sprintf('Psql "%s" version must be installed.', self::PSQL_VERSION),
            sprintf('Upgrade <strong>Psql</strong> to "%s" version.', self::PSQL_VERSION)
        );

        $postgresDaemonChecker = new PostgresDaemonChecker();
        $this->addSystemRequirement(
            $postgresDaemonChecker->isDaemonRunning(),
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

        $mailcatcherDaemonChecker = new MailcatcherDaemonChecker();
        $this->addSystemRequirement(
            $mailcatcherDaemonChecker->isDaemonRunning(),
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
    public function addSystemRequirement($fulfilled, $testMessage, $helpHtml, $helpText = null)
    {
        $this->add(new SystemRequirement($fulfilled, $testMessage, $helpHtml, $helpText, false));
    }
}
