<?php

namespace Programgames\OroDev\Requirements\System;

use Programgames\OroDev\Requirements\Tools\ElasticSearchDaemonChecker;
use Programgames\OroDev\Requirements\Tools\ElasticSearchExecutableFinder;
use Programgames\OroDev\Requirements\Tools\ElasticSearchVersionChecker;
use Programgames\OroDev\Requirements\Tools\RabbitMqDaemonChecker;
use Programgames\OroDev\Requirements\Tools\RabbitMQExecutableFinder;
use Programgames\OroDev\Requirements\Tools\RabbitMqVersionChecker;

trait ESRabbitCheckerTrait
{
    abstract public function addSystemRequirement(bool $elasticSearchExist, string $sprintf, string $param);

    public function checkEsRabbitMq(string $eSVersion, string $rabbitMqVersion)
    {
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
            $rabbitMQVersionChecker->satisfies('rabbitmqctl', $rabbitMqVersion),
            sprintf('RabbitMQ "%s" version must be installed', $rabbitMqVersion),
            sprintf(
                'Upgrade <strong>RabbitMQ</strong> to "%s" version. ! => check that RabbitMQ daemon is running first',
                $rabbitMqVersion
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
