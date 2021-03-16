<?php

namespace Programgames\OroDev\Requirements\System;

use Programgames\OroDev\Tools\DaemonChecker\ElasticSearchDaemonChecker;
use Programgames\OroDev\Tools\DaemonChecker\RabbitMqDaemonChecker;
use Programgames\OroDev\Tools\ExecutableFinder\RabbitMQExecutableFinder;
use Programgames\OroDev\Tools\VersionChecker\ElasticSearchVersionChecker;
use Programgames\OroDev\Tools\VersionChecker\RabbitMqVersionChecker;

interface ESAndRabbitCheckerInterface
{
    public function checkEsRabbitMq(
        ElasticSearchDaemonChecker $elasticSearchDaemonChecker,
        ElasticSearchVersionChecker $elasticSearchVersionChecker,
        RabbitMqDaemonChecker $rabbitMqDaemonChecker,
        RabbitMQExecutableFinder $rabbitMQExecutableFinder,
        RabbitMqVersionChecker $rabbitMqVersionChecker,
        string $eSVersion,
        string $rabbitMqVersion
    );
}
