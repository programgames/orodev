<?php

namespace Programgames\OroDev\Requirements\System;

use Programgames\OroDev\Tools\DaemonChecker\ElasticSearchDaemonChecker;
use Programgames\OroDev\Tools\DaemonChecker\MailcatcherDaemonChecker;
use Programgames\OroDev\Tools\DaemonChecker\PostgresDaemonChecker;
use Programgames\OroDev\Tools\DaemonChecker\RabbitMqDaemonChecker;
use Programgames\OroDev\Tools\ExecutableFinder\MailcatcherExecutableFinder;
use Programgames\OroDev\Tools\ExecutableFinder\PostgresExecutableFinder;
use Programgames\OroDev\Tools\ExecutableFinder\PsqlExecutableFinder;
use Programgames\OroDev\Tools\ExecutableFinder\RabbitMQExecutableFinder;
use Programgames\OroDev\Tools\VersionChecker\PostgresVersionChecker;
use Programgames\OroDev\Tools\VersionChecker\PsqlVersionChecker;
use Programgames\OroDev\Tools\VersionChecker\RabbitMqVersionChecker;

class OroPlatform4EESystemRequirements extends OroPlatform4CESystemRequirements implements ESAndRabbitCheckerInterface
{
    const ELASTIC_SEARCH_VERSION = "7.*";
    const RABBIT_MQ_VERSION = ">=3.7.21";

    use ESRabbitCheckerTrait;

    /**
     * OroDevRequirements constructor.
     * @param MailcatcherExecutableFinder $mailcatcherExecutableFinder
     * @param MailcatcherDaemonChecker $mailcatcherDaemonChecker
     * @param PostgresDaemonChecker $postgresDaemonChecker
     * @param PostgresExecutableFinder $postgresExecutableFinder
     * @param PsqlExecutableFinder $psqlExecutableFinder
     * @param PostgresVersionChecker $postgresVersionChecker
     * @param PsqlVersionChecker $psqlVersionChecker
     * @param ElasticSearchDaemonChecker $elasticSearchDaemonChecker
     * @param RabbitMqDaemonChecker $rabbitMqDaemonChecker
     * @param RabbitMQExecutableFinder $rabbitMQExecutableFinder
     * @param RabbitMqVersionChecker $rabbitMqVersionChecker
     */
    public function __construct(
        MailcatcherExecutableFinder $mailcatcherExecutableFinder,
        MailcatcherDaemonChecker $mailcatcherDaemonChecker,
        PostgresDaemonChecker $postgresDaemonChecker,
        PostgresExecutableFinder $postgresExecutableFinder,
        PsqlExecutableFinder $psqlExecutableFinder,
        PostgresVersionChecker $postgresVersionChecker,
        PsqlVersionChecker $psqlVersionChecker,
        ElasticSearchDaemonChecker $elasticSearchDaemonChecker,
        RabbitMqDaemonChecker $rabbitMqDaemonChecker,
        RabbitMQExecutableFinder $rabbitMQExecutableFinder,
        RabbitMqVersionChecker $rabbitMqVersionChecker
    ) {
        parent::__construct(
            $mailcatcherDaemonChecker,
            $postgresDaemonChecker,
            $mailcatcherExecutableFinder,
            $postgresExecutableFinder,
            $psqlExecutableFinder,
            $postgresVersionChecker,
            $psqlVersionChecker
        );

        $this->checkEsRabbitMq(
            $elasticSearchDaemonChecker,
            $rabbitMqDaemonChecker,
            $rabbitMQExecutableFinder,
            $rabbitMqVersionChecker,
            self::ELASTIC_SEARCH_VERSION,
            self::RABBIT_MQ_VERSION
        );
    }
}
