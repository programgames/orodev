<?php

namespace Programgames\OroDev\Config;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class ConfigHelper
{
    public static function getParameter($parameter)
    {
        $directory = __DIR__ . '/../../config/config.yml';

        $configFile = file_get_contents($directory);
        $config = Yaml::parse($configFile);

        $processor = new Processor();
        $databaseConfiguration = new OroDevConfig();
        $processedConfiguration = $processor->processConfiguration(
            $databaseConfiguration,
            $config
        );
    }
}
