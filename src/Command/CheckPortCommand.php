<?php

namespace Programgames\OroDev\Command;

use Programgames\OroDev\Requirements\Tools\ElasticSearchDaemonChecker;
use Programgames\OroDev\Requirements\Tools\KibanaDaemonChecker;
use Programgames\OroDev\Requirements\Tools\MailcatcherDaemonChecker;
use Programgames\OroDev\Requirements\Tools\PostgresDaemonChecker;
use Programgames\OroDev\Requirements\Tools\RabbitMqDaemonChecker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckPortCommand extends ColoredCommand
{
    public static $defaultName = 'check:port';

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
        $this->info($output, 'Mailcatcher Port : ' . MailcatcherDaemonChecker::getRunningPort());
        $this->info($output, 'Mailcatcher interface Port : ' . MailcatcherDaemonChecker::getWebInterfacePort());
        $this->info($output, 'ElasticSearch Port : ' . ElasticSearchDaemonChecker::getRunningPort());
        $this->info($output, 'Kibana Port : ' . KibanaDaemonChecker::getRunningPort());
        $this->info($output, 'Postgres Port : ' . PostgresDaemonChecker::getRunningPort());
        $this->info($output, 'RabbitMQ Port : ' . RabbitMqDaemonChecker::getRunningPort());
        $this->info($output, 'RabbitMQ interface Port : ' . RabbitMqDaemonChecker::getWebInterfacePort());

        return 0;
    }
}
