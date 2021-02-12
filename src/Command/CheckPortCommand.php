<?php

namespace Programgames\OroDev\Command;

use Programgames\OroDev\Tools\DaemonChecker\ElasticSearchDaemonChecker;
use Programgames\OroDev\Tools\DaemonChecker\KibanaDaemonChecker;
use Programgames\OroDev\Tools\DaemonChecker\MailcatcherDaemonChecker;
use Programgames\OroDev\Tools\DaemonChecker\PostgresDaemonChecker;
use Programgames\OroDev\Tools\DaemonChecker\RabbitMqDaemonChecker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckPortCommand extends ColoredCommand
{
    public static $defaultName = 'check:port';

    /** @var MailcatcherDaemonChecker */
    private $mailcatcherDaemonChecker;

    /** @var ElasticSearchDaemonChecker */
    private $elasticSearchDaemonChecker;

    /** @var KibanaDaemonChecker */
    private $kibanaDaemonChecker;

    /** @var PostgresDaemonChecker */
    private $postgresDaemonChecker;

    /** @var RabbitMqDaemonChecker */
    private $rabbitMqDaemonChecker;

    /**
     * CheckPortCommand constructor.
     * @param MailcatcherDaemonChecker $mailcatcherDaemonChecker
     * @param ElasticSearchDaemonChecker $elasticSearchDaemonChecker
     * @param KibanaDaemonChecker $kibanaDaemonChecker
     * @param PostgresDaemonChecker $postgresDaemonChecker
     * @param RabbitMqDaemonChecker $rabbitMqDaemonChecker
     */
    public function __construct(
        MailcatcherDaemonChecker $mailcatcherDaemonChecker,
        ElasticSearchDaemonChecker $elasticSearchDaemonChecker,
        KibanaDaemonChecker $kibanaDaemonChecker,
        PostgresDaemonChecker $postgresDaemonChecker,
        RabbitMqDaemonChecker $rabbitMqDaemonChecker
    ) {
        parent::__construct();

        $this->mailcatcherDaemonChecker = $mailcatcherDaemonChecker;
        $this->elasticSearchDaemonChecker = $elasticSearchDaemonChecker;
        $this->kibanaDaemonChecker = $kibanaDaemonChecker;
        $this->postgresDaemonChecker = $postgresDaemonChecker;
        $this->rabbitMqDaemonChecker = $rabbitMqDaemonChecker;
    }

    protected function configure()
    {
        $this
            ->setDescription('Checks application ports')
            ->addOption('env', null, InputOption::VALUE_OPTIONAL, 'environment of the application', 'dev')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command checks ports of services.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->info($output, 'Mailcatcher Port : ' . $this->mailcatcherDaemonChecker->getRunningPort());
        $this->info($output, 'Mailcatcher interface Port : ' . $this->mailcatcherDaemonChecker->getWebInterfacePort());
        $this->info($output, 'ElasticSearch Port : ' . $this->elasticSearchDaemonChecker->getRunningPort());
        $this->info($output, 'Kibana Port : ' .$this->kibanaDaemonChecker->getRunningPort());
        $this->info($output, 'Postgres Port : ' . $this->postgresDaemonChecker->getRunningPort());
        $this->info($output, 'RabbitMQ Port : ' . $this->rabbitMqDaemonChecker->getRunningPort());
        $this->info($output, 'RabbitMQ interface Port : ' . $this->rabbitMqDaemonChecker->getWebInterfacePort());

        return 0;
    }
}
