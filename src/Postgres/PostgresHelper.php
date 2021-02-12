<?php

namespace Programgames\OroDev\Postgres;

use Programgames\OroDev\Config\ConfigHelper;
use Programgames\OroDev\Exception\ParameterNotFoundException;
use RuntimeException;
use Symfony\Component\Process\Process;

class PostgresHelper
{
    /**
     * @return array
     * @throws ParameterNotFoundException
     */
    public static function getDatabases(): array
    {
        $user = ConfigHelper::getParameter('service.postgres.user');
        $password = ConfigHelper::getParameter('service.postgres.password');

        $process = new Process(
            [
                'psql',
                '-U',
                $user,
                '-d',
                'postgres',
                '-c',
                'SELECT datname FROM pg_database;'
            ],
            null,
            ['PGPASSWORD' => $password]
        );

        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('Failed to check "%s" program. %s, command not found', 'postgres', $process->getErrorOutput())
            );
        }
        $databases = explode("\n", $process->getOutput());

        array_shift($databases);
        array_shift($databases);
        array_pop($databases);
        array_pop($databases);
        array_pop($databases);


        foreach ($databases as $key => $database) {
            $databases[$key] = trim($database);
        }


        return $databases;
    }
}
