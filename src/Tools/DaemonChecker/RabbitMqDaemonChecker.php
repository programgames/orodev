<?php

namespace Programgames\OroDev\Tools\DaemonChecker;

use RuntimeException;
use Symfony\Component\Process\Process;

class RabbitMqDaemonChecker implements DaemonCheckerInterface, WebInterfaceInterface
{
    public function isDaemonRunning(): bool
    {
        $process = new Process(['rabbitmqctl', 'status']);
        $process->run();
        if (!$process->isSuccessful()) {
            return false;
        }
        $postgresOutput = $process->getOutput();
        return !preg_match('/.*error.*/', $postgresOutput, $matches);
    }

    public function getRunningPort(): int
    {
        $baseConfig = parse_ini_file('/usr/local/etc/rabbitmq/rabbitmq-env.conf');
        if (array_key_exists('CONFIG_FILE', $baseConfig)) {
            //v3.7.0+
            $rabbitConfig = parse_ini_file(
                strtr(
                    '%path%.%extension%',
                    [
                        '%path%' => $baseConfig['CONFIG_FILE'],
                        '%extension%' => 'conf'
                    ]
                )
            );
            if (array_key_exists('listeners.tcp.default', $rabbitConfig)) {
                return $rabbitConfig['listeners.tcp.default'];
            }
        } elseif (array_key_exists('RABBITMQ_CONFIG_FILE', $baseConfig)) {
            //TODO update
            throw new RuntimeException('Not implemented yet');
        } else {
            throw new RuntimeException('RabbitMQ config file not found');
        }

        return 5672;
    }

    public function getPid(): int
    {
        //TODO implement
        return 0;
    }

    public function getWebInterfacePort(): int
    {
        $baseConfig = parse_ini_file('/usr/local/etc/rabbitmq/rabbitmq-env.conf');
        if (array_key_exists('CONFIG_FILE', $baseConfig)) {
            //v3.7.0+
            $rabbitConfig = parse_ini_file(
                strtr(
                    '%path%.%extension%',
                    [
                        '%path%' => $baseConfig['CONFIG_FILE'],
                        '%extension%' => 'conf'
                    ]
                )
            );
            if (array_key_exists('management.listener.port', $rabbitConfig)) {
                return $rabbitConfig['management.listener.port'];
            }
        } elseif (array_key_exists('RABBITMQ_CONFIG_FILE', $baseConfig)) {
            //TODO update
            throw new RuntimeException('Not implemented yet');
        } else {
            throw new RuntimeException('RabbitMQ config file not found');
        }

        return 15672;
    }
}
