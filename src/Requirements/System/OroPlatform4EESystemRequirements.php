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

class OroPlatform4EESystemRequirements extends OroPlatform4CESystemRequirements
{
    const ELASTIC_SEARCH_VERSION = "7.*";
    const RABBIT_MQ_VERSION = ">=3.7.21";

    /**
     * OroDevRequirements constructor.
     */
    public function __construct($env = 'prod')
    {
        parent::__construct($env);

        $elasticSearchFinder = new ELasticSearchExecutableFinder();
        $elasticSearchExecutable = $elasticSearchFinder->findExecutable();
        $elasticSearchExist = null !== $elasticSearchExecutable;
        $this->addSystemRequirement(
            $elasticSearchExist,
            sprintf('ElasticSearch must be installed'),
            $elasticSearchExist ? 'ElasticSearch is installed' : 'ElasticSearch must be installed'
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


        $rabbitMQExecutableFinder = new RabbitMQExecutableFinder();
        $rabbitMQExecutable = $rabbitMQExecutableFinder->findExecutable();
        $rabbitMQExist = null !== $rabbitMQExecutable;
        $this->addSystemRequirement(
            $rabbitMQExist,
            sprintf('RabbitMQ must be installed'),
            $rabbitMQExist ? 'RabbitMQ is installed' : 'RabbitMQ must be installed'
        );

        $rabbitMQVersionChecker = new RabbitMqVersionChecker();
        $this->addSystemRequirement(
            $rabbitMQVersionChecker->satisfies('rabbitmqctl', self::RABBIT_MQ_VERSION),
            sprintf('RabbitMQ "%s" version must be installed', self::RABBIT_MQ_VERSION),
            sprintf(
                'Upgrade <strong>RabbitMQ</strong> to "%s" version. ! => check that RabbitMQ daemon is running first',
                self::RABBIT_MQ_VERSION
            )
        );

        $rabbitMQDaemonChecker = new RabbitMqDaemonChecker();
        $this->addSystemRequirement(
            $rabbitMQDaemonChecker->isDaemonRunning(),
            sprintf('RabbitMQ daemon must be running'),
            sprintf('Run the RabbitMQ daemon')
        );
    }
}
