<?php

namespace Programgames\OroDev\Command;

use MCStreetguy\ComposerParser\ComposerJson;
use Programgames\OroDev\Requirements\RenderTableTrait;
use Programgames\OroDev\Requirements\System\OroCommerce3EESystemRequirements;
use Programgames\OroDev\Requirements\System\OroCommerce4EESystemRequirements;
use Programgames\OroDev\Requirements\System\OroPlatform4CESystemRequirements;
use Programgames\OroDev\Tools\DaemonChecker\ElasticSearchDaemonChecker;
use Programgames\OroDev\Tools\DaemonChecker\MailcatcherDaemonChecker;
use Programgames\OroDev\Tools\DaemonChecker\PostgresDaemonChecker;
use Programgames\OroDev\Tools\DaemonChecker\RabbitMqDaemonChecker;
use Programgames\OroDev\Tools\ExecutableFinder\MailcatcherExecutableFinder;
use Programgames\OroDev\Tools\ExecutableFinder\PostgresExecutableFinder;
use Programgames\OroDev\Tools\ExecutableFinder\PsqlExecutableFinder;
use Programgames\OroDev\Tools\ExecutableFinder\RabbitMQExecutableFinder;
use Programgames\OroDev\Tools\VersionChecker\ElasticSearchVersionChecker;
use Programgames\OroDev\Tools\VersionChecker\PostgresVersionChecker;
use Programgames\OroDev\Tools\VersionChecker\PsqlVersionChecker;
use Programgames\OroDev\Tools\VersionChecker\RabbitMqVersionChecker;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Requirements\RequirementCollection;

class CheckSystemRequirementsCommand extends Command
{
    public static $defaultName = 'check:system';
    /** @var ElasticSearchDaemonChecker */
    private $elasticSearchDaemonChecker;
    /** @var ElasticSearchVersionChecker */
    private $elasticSearchVersionChecker;
    /** @var MailcatcherExecutableFinder */
    private $mailCatcherExecutableFinder;
    /** @var MailcatcherDaemonChecker */
    private $mailcatcherDaemonChecker;
    /** @var PostgresDaemonChecker */
    private $postgresDaemonChecker;
    /** @var PostgresExecutableFinder */
    private $postgresExecutableFinder;
    /** @var PostgresVersionChecker */
    private $postgresVersionChecker;
    /** @var PsqlExecutableFinder */
    private $psqlExecutableFinder;
    /** @var PsqlVersionChecker */
    private $psqlVersionChecker;
    /** @var RabbitMQExecutableFinder */
    private $rabbitMQExecutableFinder;
    /** @var RabbitMqVersionChecker */
    private $rabbitMQVersionChecker;
    /** @var RabbitMqDaemonChecker */
    private $rabbitMqDaemonChecker;

    /**
     * CheckSystemRequirementsCommand constructor.
     * @param ElasticSearchDaemonChecker $elasticSearchDaemonChecker
     * @param ElasticSearchVersionChecker $elasticSearchVersionChecker
     * @param MailcatcherExecutableFinder $mailCatcherExecutableFinder
     * @param MailcatcherDaemonChecker $mailcatcherDaemonChecker
     * @param PostgresDaemonChecker $postgresDaemonChecker
     * @param PostgresExecutableFinder $postgresExecutableFinder
     * @param PostgresVersionChecker $postgresVersionChecker
     * @param PsqlExecutableFinder $psqlExecutableFinder
     * @param PsqlVersionChecker $psqlVersionChecker
     * @param RabbitMQExecutableFinder $rabbitMQExecutableFinder
     * @param RabbitMqDaemonChecker $rabbitMqDaemonChecker
     * @param RabbitMqVersionChecker $rabbitMQVersionChecker
     */
    public function __construct(
        ElasticSearchDaemonChecker $elasticSearchDaemonChecker,
        ElasticSearchVersionChecker $elasticSearchVersionChecker,
        MailcatcherExecutableFinder $mailCatcherExecutableFinder,
        MailcatcherDaemonChecker $mailcatcherDaemonChecker,
        PostgresDaemonChecker $postgresDaemonChecker,
        PostgresExecutableFinder $postgresExecutableFinder,
        PostgresVersionChecker $postgresVersionChecker,
        PsqlExecutableFinder $psqlExecutableFinder,
        PsqlVersionChecker $psqlVersionChecker,
        RabbitMQExecutableFinder $rabbitMQExecutableFinder,
        RabbitMqDaemonChecker $rabbitMqDaemonChecker,
        RabbitMqVersionChecker $rabbitMQVersionChecker
    ) {
        parent::__construct();

        $this->elasticSearchDaemonChecker = $elasticSearchDaemonChecker;
        $this->elasticSearchVersionChecker = $elasticSearchVersionChecker;
        $this->mailCatcherExecutableFinder = $mailCatcherExecutableFinder;
        $this->mailcatcherDaemonChecker = $mailcatcherDaemonChecker;
        $this->postgresDaemonChecker = $postgresDaemonChecker;
        $this->postgresExecutableFinder = $postgresExecutableFinder;
        $this->postgresVersionChecker = $postgresVersionChecker;
        $this->psqlExecutableFinder = $psqlExecutableFinder;
        $this->psqlVersionChecker = $psqlVersionChecker;
        $this->rabbitMQExecutableFinder = $rabbitMQExecutableFinder;
        $this->rabbitMqDaemonChecker = $rabbitMqDaemonChecker;
        $this->rabbitMQVersionChecker = $rabbitMQVersionChecker;
    }

    use RenderTableTrait;

    protected function configure(): void
    {
        $this
            ->setDescription('Checks that the system meets the requirements.')
            ->addOption('env', null, InputOption::VALUE_OPTIONAL, 'environment of the application', 'dev')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command checks that the system meets the requirements.

By default this command shows only errors, but you can specify the verbosity level to see warnings
and information messages, e.g.:

  <info>php %command.full_name% -v</info>
or
  <info>php %command.full_name% -vv</info>

The process exit code will be 0 if all requirements are met and 1 if at least one requirement is not fulfilled.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Check system requirements');

        $oroSystemRequirements = $this->getOroSystemRequirements();
        $this->renderTable($oroSystemRequirements->getRequirements(), 'Optional recommendations', $output);

        $exitCode = 0;
        $numberOfFailedRequirements = count(
            $oroSystemRequirements->getFailedRequirements()
        );
        if ($numberOfFailedRequirements > 0) {
            $exitCode = 1;
            if ($numberOfFailedRequirements > 1) {
                $output->writeln(
                    sprintf(
                        '<error>Found %d not fulfilled requirements</error>',
                        $numberOfFailedRequirements
                    )
                );
            } else {
                $output->writeln('<error>Found 1 not fulfilled requirement</error>');
            }
        } else {
            $output->writeln('<info>The application meets all mandatory requirements</info>');
        }

        return $exitCode;
    }

    /**
     * @return RequirementCollection
     */
    protected function getOroSystemRequirements()
    {
        $content = json_decode(file_get_contents(getcwd() . '/composer.json'), true);
        if ($content === null) {
            throw new RuntimeException('composer.json not found');
        }
        $composerJson = new ComposerJson($content);

        $require = $composerJson->getRequire()->getData();
        if (array_key_exists('oro/commerce-enterprise', $require)) {
            $version = $require['oro/commerce-enterprise'];
            if (preg_match('/4./', $version)) {
                return new OroCommerce4EESystemRequirements(
                    $this->mailCatcherExecutableFinder,
                    $this->mailcatcherDaemonChecker,
                    $this->postgresDaemonChecker,
                    $this->postgresExecutableFinder,
                    $this->psqlExecutableFinder,
                    $this->postgresVersionChecker,
                    $this->psqlVersionChecker,
                    $this->elasticSearchDaemonChecker,
                    $this->elasticSearchVersionChecker,
                    $this->rabbitMqDaemonChecker,
                    $this->rabbitMQExecutableFinder,
                    $this->rabbitMQVersionChecker
                );
            }

            if (preg_match('/3./', $version)) {
                return new OroCommerce3EESystemRequirements(
                    $this->mailCatcherExecutableFinder,
                    $this->mailcatcherDaemonChecker,
                    $this->postgresDaemonChecker,
                    $this->postgresExecutableFinder,
                    $this->psqlExecutableFinder,
                    $this->postgresVersionChecker,
                    $this->psqlVersionChecker,
                    $this->elasticSearchDaemonChecker,
                    $this->elasticSearchVersionChecker,
                    $this->rabbitMqDaemonChecker,
                    $this->rabbitMQExecutableFinder,
                    $this->rabbitMQVersionChecker
                );
            }

            throw new RuntimeException('Application version not supported');
        }

        if (array_key_exists('oro/commerce', $require)) {
            $version = $require['oro/commerce'];
            if (preg_match('/4./', $version)) {
                return new OroPlatform4CESystemRequirements(
                    $this->mailcatcherDaemonChecker,
                    $this->postgresDaemonChecker,
                    $this->mailCatcherExecutableFinder,
                    $this->postgresExecutableFinder,
                    $this->psqlExecutableFinder,
                    $this->postgresVersionChecker,
                    $this->psqlVersionChecker
                );
            }
            if (preg_match('/3./', $version)) {
                throw new RuntimeException('Not supported yet');
            }
            throw new RuntimeException('Application version not supported');
        }

        if (array_key_exists('oro/platform', $require)) {
            $version = $require['oro/platform'];
            if (preg_match('/4./', $version)) {
                return new OroPlatform4CESystemRequirements(
                    $this->mailcatcherDaemonChecker,
                    $this->postgresDaemonChecker,
                    $this->mailCatcherExecutableFinder,
                    $this->postgresExecutableFinder,
                    $this->psqlExecutableFinder,
                    $this->postgresVersionChecker,
                    $this->psqlVersionChecker
                );
            }

            if (preg_match('/3./', $version)) {
                throw new RuntimeException('Not supported yet');
            }
            throw new RuntimeException('Application version not supported');
        }
        throw new RuntimeException('Application not supported');
    }
}
