<?php

namespace Programgames\OroDev\Tools\DaemonChecker;

use Exception;
use PDO;
use Programgames\OroDev\Exception\NotSupportedException;
use Programgames\OroDev\Requirements\YamlFileLoader;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Process\Process;

class PostgresDaemonChecker implements DaemonCheckerInterface
{
    public function isDaemonRunning(): bool
    {
        $process = new Process(['pg_isready']);
        $process->run();
        if (!$process->isSuccessful()) {
            return false;
        }
        $postgresOutput = $process->getOutput();
        return preg_match('/accepting connections/', $postgresOutput, $matches);
    }

    public function getRunningPort(): int
    {
        $process = new Process(['pg_isready']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('Failed to check "%s" daemon. %s, command not found', 'postgres', $process->getErrorOutput())
            );
        }
        $postgresOutput = $process->getOutput();
        preg_match('/\d+/', $postgresOutput, $version);
        return $version[0];
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getPid(): int
    {
        $conn = $this->getDatabaseConnection();
        return $conn->query("select pg_backend_pid()")->fetchColumn();
    }

    /**
     * @noinspection DuplicatedCode
     * @throws Exception
     */
    private function getDatabaseConnection(): ?PDO
    {
        $baseDir = getcwd();
        $env = 'prod';
        $configYmlPath = $baseDir . '/config/config_' . $env . '.yml';
        if (is_file($configYmlPath)) {
            $config = $this->getParameters($configYmlPath);


            $driver = str_replace('pdo_', '', $config['database_driver']);
            if ($driver !== 'pgsql') {
                throw new NotSupportedException($driver . ' not supported');
            }
            $dsnParts = array(
                'host=' . $config['database_host'],
            );
            if (!empty($config['database_port'])) {
                $dsnParts[] = 'port=' . $config['database_port'];
            }
            $dsnParts[] = 'dbname=' . $config['database_name'];

            try {
                return new PDO(
                    $driver . ':' . implode(';', $dsnParts),
                    $config['database_user'],
                    $config['database_password']
                );
            } catch (Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * @param string $parametersYmlPath
     * @return array
     * @throws Exception
     */
    protected function getParameters(string $parametersYmlPath): array
    {
        $fileLocator = new FileLocator();
        $loader = new YamlFileLoader($fileLocator);

        return $loader->load($parametersYmlPath);
    }
}
