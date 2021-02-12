<?php

namespace Programgames\OroDev\Command;

use Programgames\OroDev\Config\ConfigHelper;
use Programgames\OroDev\Exception\ParameterNotFoundException;
use Programgames\OroDev\Postgres\PostgresHelper;
use Programgames\OroDev\Tools\DaemonChecker\PostgresDaemonChecker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class ConfigureProjectCommand extends ColoredCommand
{
    public static $defaultName = 'configure';

    /** @var PostgresDaemonChecker */
    private $postgresDaemonChecker;

    /**
     * ConfigureProjectCommand constructor.
     * @param PostgresDaemonChecker $postgresDaemonChecker
     */
    public function __construct(PostgresDaemonChecker $postgresDaemonChecker)
    {
        parent::__construct();

        $this->postgresDaemonChecker = $postgresDaemonChecker;
    }

    protected function configure()
    {
        $this
            ->setDescription('Configure parameters.yml')
            ->addOption('env', null, InputOption::VALUE_OPTIONAL, 'environment of the application', 'dev')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command checks ports of services.
EOT
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ParameterNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = Yaml::parseFile('config/parameters.yml');
        $config = $this->configureDatabaseConfig($input, $output, $config);
        $config = $this->configureMailConfig($config);
        $config = $this->configureWebsocketConfig($config);
        $config = $this->configureSearchEngineConfig($input, $output, $config);
        $yaml = Yaml::dump($config);

        file_put_contents('config/parameters.yml', $yaml);

        return 0;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $config
     * @return mixed
     * @throws ParameterNotFoundException
     */
    private function configureDatabaseConfig(InputInterface $input, OutputInterface $output, $config)
    {
        $config['parameters']['database_driver'] = 'pdo_pgsql';
        $config['parameters']['database_host'] = 'localhost';
        $config['parameters']['database_port'] = $this->postgresDaemonChecker->getRunningPort();

        $this->info($output, 'Available databases');

        $databases = PostgresHelper::getDatabases();
        foreach ($databases as $database) {
            $this->info($output, $database);
        }
        $callback = function (string $userInput) use ($databases): array {
            return array_filter(
                $databases,
                function ($database) use ($userInput) {
                    if ($userInput === null || empty($userInput)) {
                        return null;
                    }
                    if (preg_match('/.*'. $userInput . '.*\b/', $database)) {
                        return $database;
                    }
                    return null;
                }
            );
        };

        $helper = $this->getHelper('question');
        $question = new Question('Please enter the name of the database : ', 'postgres');
        $question->setAutocompleterCallback($callback);
        $database = $helper->ask($input, $output, $question);
        $config['parameters']['database_name'] = $database;
        $config['parameters']['database_user'] = ConfigHelper::getParameter('service.postgres.user');
        $config['parameters']['database_password'] = ConfigHelper::getParameter('service.postgres.password');


        return $config;
    }

    private function configureMailConfig($config): array
    {
        $config['parameters']['mailer_transport'] = 'smtp';
        $config['parameters']['mailer_host'] = 'localhost';
        $config['parameters']['mailer_port'] = 'localhost';
        $config['parameters']['mailer_encryption'] = null;
        $config['parameters']['mailer_user'] = null;
        $config['parameters']['mailer_password'] = null;

        return $config;
    }
    
    private function configureWebsocketConfig(array $config): array
    {
        $config['parameters']['websocket_bind_address'] = '0.0.0.0';
        $config['parameters']['websocket_bind_port'] = 8080;
        $config['parameters']['websocket_frontend_host'] = '*';
        $config['parameters']['websocket_frontend_port'] = 8080;
        $config['parameters']['websocket_frontend_path'] = '';
        $config['parameters']['websocket_backend_host'] = '*';
        $config['parameters']['websocket_backend_port'] = 8080;
        $config['parameters']['websocket_backend_path'] = '';
        $config['parameters']['websocket_backend_transport'] = 'tcp';
        $config['parameters']['websocket_backend_ssl_context_options'] = [];

        return $config;
    }

    private function configureSearchEngineConfig(InputInterface $input, OutputInterface $output, array $config): array
    {

        /*
         *     search_engine_name: orm
    search_engine_host: localhost
    search_engine_port: null
    search_engine_index_prefix: oro_search
    search_engine_username: elastic
    search_engine_password: changeme
    search_engine_ssl_verification: null
    search_engine_ssl_cert: null
    search_engine_ssl_cert_password: null
    search_engine_ssl_key: null
    search_engine_ssl_key_password: null
    website_search_engine_index_prefix: oro_website_search
         */

        return $config;
    }
}
