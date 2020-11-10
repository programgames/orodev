<?php

namespace Programgames\OroDev\Requirements;

use Programgames\OroDev\Requirements\Tools\ElasticSearchDaemonChecker;
use Programgames\OroDev\Requirements\Tools\ElasticSearchExecutableFinder;
use Programgames\OroDev\Requirements\Tools\PostgresDaemonChecker;
use Programgames\OroDev\Requirements\Tools\PostgresExecutableFinder;
use Programgames\OroDev\Requirements\Tools\ElasticSearchVersionChecker;
use Programgames\OroDev\Requirements\Tools\PostgresVersionChecker;
use Programgames\OroDev\Requirements\Tools\PsqlExecutableFinder;
use Programgames\OroDev\Requirements\Tools\PsqlVersionChecker;
use Symfony\Requirements\RequirementCollection;

class OroCommerce4SystemRequirements extends RequirementCollection
{
    public const POSTGRES_VERSION = "~9.6";
    public const PSQL_VERSION = "~9.6";
    const ELASTIC_SEARCH_VERSION = "7.*";

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
            $postgresExist ? 'Postgres is installed' : 'Postgres must be installed',

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
            $psqlExist ? 'Psql is installed' : 'Psql must be installed',
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

        $elasticSearchFinder = new ELasticSearchExecutableFinder();
        $elasticSearchExecutable = $elasticSearchFinder->findExecutable();
        $elasticSearchExist = null !== $elasticSearchExecutable;
        $this->addSystemRequirement(
            $elasticSearchExist,
            sprintf('ElasticSearch must be installed'),
            $elasticSearchExist ? 'ElasticSearch is installed' : 'ElasticSearch must be installed',
        );

        $elasticSearchVersionChecker = new ElasticSearchVersionChecker();
        $this->addSystemRequirement(
            $elasticSearchVersionChecker->satisfies($elasticSearchExecutable, self::ELASTIC_SEARCH_VERSION),
            sprintf('ElasticSearch "%s" version must be installed.', self::ELASTIC_SEARCH_VERSION),
            sprintf('Upgrade <strong>ElasticSearch</strong> to "%s" version.', self::ELASTIC_SEARCH_VERSION)
        );

        $elasticSearchDaemonChecker = new ElasticSearchDaemonChecker();
        $this->addSystemRequirement(
            $elasticSearchDaemonChecker->isDaemonRunning(),
            sprintf('ElasticSearch daemon must be running'),
            sprintf('Run the ElasticSearch daemon')
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
