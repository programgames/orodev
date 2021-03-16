<?php

namespace Programgames\OroDev\Postgres;

use Programgames\OroDev\Config\ConfigHelper;
use Programgames\OroDev\Exception\ParameterNotFoundException;
use RuntimeException;
use Symfony\Component\Process\Process;

class PostgresHelper
{
    /** @var ConfigHelper */
    private $configHelper;

    /**
     * PostgresHelper constructor.
     * @param ConfigHelper $configHelper
     */
    public function __construct(ConfigHelper $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    /**
     * @param string $database
     * @throws ParameterNotFoundException
     */
    public function createDatabase(string $database):void
    {
        $process = new Process(
            [
                'createdb',
                $database
            ]
        );

        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('Failed to use "%s" program. %s, command not found', 'createdb', $process->getErrorOutput())
            );
        }

        $user = $this->configHelper->getParameter('service.postgres.user');
        $password = $this->configHelper->getParameter('service.postgres.password');

        $process = new Process(
            [
                'psql',
                '-U',
                $user,
                '-d',
                $database,
                '-c',
                'create extension if not exists "uuid-ossp";'
            ],
            null,
            ['PGPASSWORD' => $password]
        );

        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('Failed to use "%s" program. %s', 'psql', $process->getErrorOutput())
            );
        }
    }

    /**
     * @return array
     * @throws ParameterNotFoundException
     */
    public function getDatabases(): array
    {
        $user = $this->configHelper->getParameter('service.postgres.user');
        $password = $this->configHelper->getParameter('service.postgres.password');

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

        $databases[] = 'Create a new database';
        return $databases;
    }
}
