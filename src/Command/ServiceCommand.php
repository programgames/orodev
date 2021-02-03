<?php

namespace Programgames\OroDev\Command;

use Programgames\OroDev\Config\ConfigHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ServiceCommand extends ColoredCommand
{
    public static $defaultName = 'service';

    protected function configure()
    {
        $this
            ->setDescription('Start service.')
            ->addArgument('mode', InputArgument::REQUIRED, 'start/stop/restart/version ')
            ->addArgument('service', InputArgument::REQUIRED, 'start/stop/restart/get version the specified service')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> start/stop/restart/version the specified service.

Actual service list : postgres, mailcatcher, rabbitmq, elasticsearch, kibana
By default this command shows only errors, but you can specify the verbosity level to see warnings
and information messages, e.g.:

  <info>php %command.full_name% -v</info>
or
  <info>php %command.full_name% -vv</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        switch ($input->getArgument('mode')) {
            case 'start':
                return $this->startService($output, $input->getArgument('service'));
            case 'stop':
                return $this->stopService($output, $input->getArgument('service'));
            case 'restart':
                return $this->restartService($output, $input->getArgument('service'));
            case 'version':
                return $this->checkServiceVersion($output, $input->getArgument('service'));
            default:
                $this->error($output, 'Unknow mode');
                return -1;
        }
    }

    protected function startService(OutputInterface $output, string $service): int
    {
        $output->writeln(sprintf('Starting service %s ...', $service));
        if (!$this->checkServiceName($service)) {
            $this->error($output, 'Unknow service');
            return -1;
        }
        $command = explode(" ", ConfigHelper::getParameter(sprintf('service.%s.start_command', $service)));
        $processCode = $this->runProcess($command, $output);
        $output->writeln(sprintf('Service %s started  ...', $service));

        return $processCode;
    }

    protected function stopService(OutputInterface $output, string $service): int
    {
        $output->writeln(sprintf('Stopping service %s ...', $service));
        if (!$this->checkServiceName($service)) {
            $this->error($output, 'Unknow service');
            return -1;
        }

        $command = explode(" ", ConfigHelper::getParameter(sprintf('service.%s.start_command', $service)));
        $processCode = $this->runProcess($command, $output);

        $output->writeln(sprintf('Service %s stopped  ...', $service));

        return $processCode;
    }

    protected function restartService(OutputInterface $output, string $service): int
    {
        $output->writeln(sprintf('Restarting service %s ...', $service));
        if (!$this->checkServiceName($service)) {
            $output->writeln('Unknow service');
            return -1;
        }

        $command = explode(" ", ConfigHelper::getParameter(sprintf('service.%s.start_command', $service)));

        $processCode = $this->runProcess($command, $output);

        $output->writeln(sprintf('Service %s restarted  ...', $service));

        return $processCode;
    }

    protected function checkServiceVersion(OutputInterface $output, string $service): int
    {
        if (!$this->checkServiceName($service)) {
            $this->error($output, 'Unknow service');
            return -1;
        }
        $command = explode(" ", ConfigHelper::getParameter(sprintf('service.%s.version_command', $service)));
        $processCode = $this->runProcess($command, $output);

        return $processCode;
    }

    protected function checkServiceName(string $serviceName): bool
    {
        return in_array(
            $serviceName,
            ['rabbitmq', 'mailcatcher', 'postgres', 'kibana', 'elasticsearch']
        );
    }

    protected function runProcess(array $command, OutputInterface $output)
    {
        $process = new Process($command);
        $process->run();
        if (!$process->isSuccessful()) {
            $this->error($output, $process->getErrorOutput());
            return -1;
        }

        $this->info($output, $process->getOutput());

        return 0;
    }
}
