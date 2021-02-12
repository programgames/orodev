<?php

namespace Programgames\OroDev\Requirements\System;

use Programgames\OroDev\Tools\DaemonChecker\ElasticSearchDaemonChecker;
use Programgames\OroDev\Tools\DaemonChecker\RabbitMqDaemonChecker;
use Programgames\OroDev\Tools\ExecutableFinder\ElasticSearchExecutableFinder;
use Programgames\OroDev\Tools\ExecutableFinder\RabbitMQExecutableFinder;
use Programgames\OroDev\Tools\VersionChecker\ElasticSearchVersionChecker;
use Programgames\OroDev\Tools\VersionChecker\RabbitMqVersionChecker;

trait ESRabbitCheckerTrait
{
    abstract public function addSystemRequirement(bool $elasticSearchExist, string $sprintf, string $param);

    public function checkEsRabbitMq(
        ElasticSearchDaemonChecker $elasticSearchDaemonChecker,
        RabbitMqDaemonChecker $rabbitMqDaemonChecker,
        RabbitMQExecutableFinder $rabbitMQExecutableFinder,
        RabbitMqVersionChecker $rabbitMqVersionChecker,
        string $eSVersion,
        string $rabbitMqVersion
    ) {
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
            $elasticSearchVersionChecker->satisfies($elasticSearchExecutable, $eSVersion),
            sprintf('ElasticSearch "%s" version must be installed.', $eSVersion),
            sprintf('Upgrade <strong>ElasticSearch</strong> to "%s" version.', $eSVersion)
        );

        $this->addSystemRequirement(
            $elasticSearchDaemonChecker->isDaemonRunning(),
            sprintf('ElasticSearch daemon must be running'),
            sprintf('Run the ElasticSearch daemon')
        );


        $rabbitMQExecutable = $rabbitMQExecutableFinder->findExecutable();
        $rabbitMQExist = null !== $rabbitMQExecutable;
        $this->addSystemRequirement(
            $rabbitMQExist,
            sprintf('RabbitMQ must be installed'),
            $rabbitMQExist ? 'RabbitMQ is installed' : 'RabbitMQ must be installed'
        );

        $this->addSystemRequirement(
            $rabbitMqVersionChecker->satisfies('rabbitmqctl', $rabbitMqVersion),
            sprintf('RabbitMQ "%s" version must be installed', $rabbitMqVersion),
            sprintf(
                'Upgrade <strong>RabbitMQ</strong> to "%s" version. ! => check that RabbitMQ daemon is running first',
                $rabbitMqVersion
            )
        );

        $this->addSystemRequirement(
            $rabbitMqDaemonChecker->isDaemonRunning(),
            sprintf('RabbitMQ daemon must be running'),
            sprintf('Run the RabbitMQ daemon')
        );
    }
}
