<?php

namespace Programgames\OroDev\Command;

use MCStreetguy\ComposerParser\ComposerJson;
use Programgames\OroDev\Config\ConfigHelper;
use Programgames\OroDev\Exception\DatabaseAlreadyExist;
use Programgames\OroDev\Exception\ParameterNotFoundException;
use Programgames\OroDev\Postgres\PostgresHelper;
use Programgames\OroDev\Tools\DaemonChecker\ElasticSearchDaemonChecker;
use Programgames\OroDev\Tools\DaemonChecker\MailcatcherDaemonChecker;
use Programgames\OroDev\Tools\DaemonChecker\PostgresDaemonChecker;
use Programgames\OroDev\Tools\DaemonChecker\RabbitMqDaemonChecker;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class ConfigureProjectCommand extends ColoredCommand
{
    public static $defaultName = 'configure';

    /** @var MailcatcherDaemonChecker */
    private $mailcatcherDaemonChecker;

    /** @var PostgresDaemonChecker */
    private $postgresDaemonChecker;

    /** @var ElasticSearchDaemonChecker */
    private $elasticSearchDaemonChecker;

    /** @var RabbitMqDaemonChecker */
    private $rabbitMQDaemonChecker;

    /** @var ConfigHelper */
    private $configHelper;

    /** @var PostgresHelper */
    private $postgresHelper;

    /**
     * ConfigureProjectCommand constructor.
     * @param MailcatcherDaemonChecker $mailcatcherDaemonChecker
     * @param PostgresDaemonChecker $postgresDaemonChecker
     * @param ElasticSearchDaemonChecker $elasticSearchDaemonChecker
     * @param RabbitMqDaemonChecker $rabbitMQDaemonChecker
     * @param ConfigHelper $configHelper
     * @param PostgresHelper $postgresHelper
     */
    public function __construct(
        MailcatcherDaemonChecker $mailcatcherDaemonChecker,
        PostgresDaemonChecker $postgresDaemonChecker,
        ElasticSearchDaemonChecker $elasticSearchDaemonChecker,
        RabbitMqDaemonChecker $rabbitMQDaemonChecker,
        ConfigHelper $configHelper,
        PostgresHelper $postgresHelper
    ) {
        parent::__construct();

        $this->mailcatcherDaemonChecker = $mailcatcherDaemonChecker;
        $this->postgresDaemonChecker = $postgresDaemonChecker;
        $this->elasticSearchDaemonChecker = $elasticSearchDaemonChecker;
        $this->rabbitMQDaemonChecker = $rabbitMQDaemonChecker;
        $this->configHelper = $configHelper;
        $this->postgresHelper = $postgresHelper;
    }

    protected function configure(): void
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
        $config = $this->configureSearchEngineConfig($config);
        $config = $this->configureRabbitMQ($config);
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

        $databases = $this->postgresHelper->getDatabases();
        foreach ($databases as $database) {
            $this->info($output, $database);
        }
        $callback = function (string $userInput) use ($databases): array {
            return array_filter(
                $databases,
                static function ($database) use ($userInput) {
                    if ($userInput === null || empty($userInput)) {
                        return null;
                    }
                    if (preg_match('/.*' . $userInput . '.*\b/', $database)) {
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
        if ($database === 'Create a new database') {
            $questionName = new Question('New database name : ');
            $database = $helper->ask($input, $output, $questionName);
            if (in_array($database, $databases, true)) {
                throw new DatabaseAlreadyExist(sprintf('The database %s already exist', $database));
            }
            $this->postgresHelper->createDatabase($database);
        }
        $config['parameters']['database_name'] = $database;
        $config['parameters']['database_user'] = $this->configHelper->getParameter('service.postgres.user');
        $config['parameters']['database_password'] = $this->configHelper->getParameter('service.postgres.password');


        return $config;
    }

    private function configureMailConfig($config): array
    {
        $config['parameters']['mailer_transport'] = 'smtp';
        $config['parameters']['mailer_host'] = 'localhost';
        $config['parameters']['mailer_port'] = $this->mailcatcherDaemonChecker->getRunningPort();
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

    /**
     * @param array $config
     * @return array
     * @throws ParameterNotFoundException
     */
    private function configureSearchEngineConfig(array $config): array
    {
        $config['parameters']['search_engine_ssl_verification'] = null;
        $config['parameters']['search_engine_ssl_cert'] = null;
        $config['parameters']['search_engine_ssl_cert_password'] = null;
        $config['parameters']['search_engine_ssl_key'] = null;
        $config['parameters']['search_engine_ssl_key_password'] = null;
        $config['parameters']['website_search_engine_index_prefix'] = sprintf(
            'oro_website_%s_search',
            $this->getCurrentDirectory()
        );
        $config['parameters']['search_engine_index_prefix'] = sprintf(
            'oro_%s_search',
            $this->getCurrentDirectory()
        );

        if (!$this->isEnterprise()) {
            $config['parameters']['search_engine_name'] = 'orm';
            $config['parameters']['search_engine_host'] = 'localhost';
            $config['parameters']['search_engine_port'] = null;
        } else {
            $config['parameters']['search_engine_name'] = 'elastic_search';
            $config['parameters']['search_engine_host'] = 'localhost';
            $config['parameters']['search_engine_port'] = $this->elasticSearchDaemonChecker->getRunningPort();
            $config['parameters']['search_engine_username'] = $this->configHelper->getParameter(
                'service.elasticsearch.user'
            );
            $config['parameters']['search_engine_password'] = $this->configHelper->getParameter(
                'service.elasticsearch.password'
            );
        }
        return $config;
    }

    private function isEnterprise(): bool
    {
        $content = json_decode(file_get_contents(getcwd() . '/composer.json'), true);
        if ($content === null) {
            throw new RuntimeException('composer.json not found');
        }
        $composerJson = new ComposerJson($content);

        $require = $composerJson->getRequire()->getData();
        if (array_key_exists('oro/commerce-enterprise', $require)) {
            return true;
        }
        return false;
    }

    private function getCurrentDirectory()
    {
        $path = getcwd();
        $position = strrpos($path, '/') + 1;
        return substr($path, $position);
    }

    /**
     * @param array $config
     * @return array
     * @throws ParameterNotFoundException
     */
    private function configureRabbitMQ(array $config): array
    {
        if (!$this->isEnterprise()) {
            $config['parameters']['message_queue_transport'] = 'dbal';
            $config['parameters']['message_queue_transport_config'] = [];
        } else {
            $config['parameters']['message_queue_transport'] = 'amqp';
            $config['parameters']['message_queue_transport_config'] = [
                'host' => 'localhost',
                'port' => $this->rabbitMQDaemonChecker->getRunningPort(),
                'user' => $this->configHelper->getParameter('service.rabbitmq.user'),
                'password' => $this->configHelper->getParameter('service.rabbitmq.password'),
                'vhost' => '/'
            ];
        }
        return $config;
    }
}
