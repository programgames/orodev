<?php

namespace Programgames\OroDev\Config;

use Programgames\OroDev\Exception\ParameterNotFoundException;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class ConfigHelper
{
    /**
     * @param $parameter
     * @return mixed
     * @throws ParameterNotFoundException
     */
    public function getParameter($parameter)
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

        $configContainer =  $this->normalizeConfig($processedConfiguration);

        if (!array_key_exists($parameter, $configContainer)) {
            throw new ParameterNotFoundException($parameter);
        }

        return $configContainer[$parameter];
    }

    private function normalizeConfig(array $config): array
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($config), RecursiveIteratorIterator::SELF_FIRST);
        $paths = [];
        foreach ($iterator as $k => $v) { // Loop thru each iterator

            if (!$iterator->callHasChildren()) {
                for ($p = array(), $i = 0, $z = $iterator->getDepth(); $i <= $z; $i++) {
                    $p[] = $iterator->getSubIterator($i)->key();
                }
                $path = implode('.', $p);
                $paths[$path] = $v;
            }
        }
        return $paths;
    }
}
