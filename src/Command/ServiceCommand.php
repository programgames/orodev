<?php

namespace Programgames\OroDev\Command;

use Programgames\OroDev\Config\ConfigHelper;
use Programgames\OroDev\Exception\ParameterNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ServiceCommand extends ColoredCommand
{
    public static $defaultName = 'service';

    /** @var ConfigHelper */
    private $configHelper;

    /**
     * ServiceCommand constructor.
     * @param ConfigHelper $configHelper
     */
    public function __construct(ConfigHelper $configHelper)
    {
        parent::__construct();
        $this->configHelper = $configHelper;
    }

    protected function configure(): void
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


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        switch ($input->getArgument('mode')) {
            case 'start':
                try {
                    return $this->startService($output, $input->getArgument('service'));
                } catch (ParameterNotFoundException $e) {
                    $this->error($output, $e->getMessage());
                    return -1;
                }
            case 'stop':
                try {
                    return $this->stopService($output, $input->getArgument('service'));
                } catch (ParameterNotFoundException $e) {
                    $this->error($output, $e->getMessage());
                    return -1;
                }
            case 'restart':
                try {
                    return $this->restartService($output, $input->getArgument('service'));
                } catch (ParameterNotFoundException $e) {
                    $this->error($output, $e->getMessage());
                    return -1;
                }
            case 'version':
                try {
                    return $this->checkServiceVersion($output, $input->getArgument('service'));
                } catch (ParameterNotFoundException $e) {
                    $this->error($output, $e->getMessage());
                    return -1;
                }
            case 'logs':
                try {
                    return $this->displayLogs($output, $input->getArgument('service'));
                } catch (ParameterNotFoundException $e) {
                    $this->error($output, $e->getMessage());
                    return -1;
                }
            case 'open':
                try {
                    return $this->open($output, $input->getArgument('service'));
                } catch (ParameterNotFoundException $e) {
                    $this->error($output, $e->getMessage());
                    return -1;
                }
            default:
                $this->error($output, 'Unknown mode');
                return -1;
        }
    }

    /**
     * @param OutputInterface $output
     * @param string $service
     * @return int
     * @throws ParameterNotFoundException
     */
    protected function startService(OutputInterface $output, string $service): int
    {
        $output->writeln(sprintf('Starting service %s ...', $service));
        if (!$this->checkServiceName($service)) {
            $this->error($output, 'Unknown service');
            return -1;
        }
        $command = explode(" ", $this->configHelper->getParameter(sprintf('service.%s.start_command', $service)));
        $processCode = $this->runProcess($command, $output);
        $output->writeln(sprintf('Service %s started  ...', $service));

        return $processCode;
    }

    /**
     * @param OutputInterface $output
     * @param string $service
     * @return int
     * @throws ParameterNotFoundException
     */
    protected function stopService(OutputInterface $output, string $service): int
    {
        $output->writeln(sprintf('Stopping service %s ...', $service));
        if (!$this->checkServiceName($service)) {
            $this->error($output, 'Unknown service');
            return -1;
        }

        $command = explode(" ", $this->configHelper->getParameter(sprintf('service.%s.stop_command', $service)));
        $processCode = $this->runProcess($command, $output);

        $output->writeln(sprintf('Service %s stopped  ...', $service));

        return $processCode;
    }

    /**
     * @param OutputInterface $output
     * @param string $service
     * @return int
     * @throws ParameterNotFoundException
     */
    protected function restartService(OutputInterface $output, string $service): int
    {
        $output->writeln(sprintf('Restarting service %s ...', $service));
        if (!$this->checkServiceName($service)) {
            $output->writeln('Unknown service');
            return -1;
        }

        $command = explode(" ", $this->configHelper->getParameter(sprintf('service.%s.restart_command', $service)));

        $processCode = $this->runProcess($command, $output);

        $output->writeln(sprintf('Service %s restarted  ...', $service));

        return $processCode;
    }

    /**
     * @param OutputInterface $output
     * @param string $service
     * @return int
     * @throws ParameterNotFoundException
     */
    protected function checkServiceVersion(OutputInterface $output, string $service): int
    {
        if (!$this->checkServiceName($service)) {
            $this->error($output, 'Unknown service');
            return -1;
        }
        $command = explode(" ", $this->configHelper->getParameter(sprintf('service.%s.version_command', $service)));
        return $this->runProcess($command, $output);
    }

    protected function checkServiceName(string $serviceName): bool
    {
        return in_array(
            $serviceName,
            ['rabbitmq', 'mailcatcher', 'postgres', 'kibana', 'elasticsearch']
        );
    }

    protected function runProcess(array $command, OutputInterface $output): int
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

    protected function streamProcess(array $command, OutputInterface $output): int
    {
        $process = new Process($command);
        $process->run(function ($type, $buffer) use ($output) {
            if (Process::ERR === $type) {
                echo $output->writeln('ERR => ' . $buffer);
            } else {
                echo $output->writeln('OUT => ' . $buffer);
            }
        });

        return 0;
    }

    /**
     * @param OutputInterface $output
     * @param string $service
     * @return int
     * @throws ParameterNotFoundException
     */
    private function displayLogs(OutputInterface $output, string $service): int
    {
        if (!$this->checkServiceName($service)) {
            $this->error($output, 'Unknown service');
            return -1;
        }
        $command = explode(" ", $this->configHelper->getParameter(sprintf('service.%s.logs_command', $service)));

        return $this->streamProcess($command, $output);
    }
    /**
     * @param OutputInterface $output
     * @param string $service
     * @return int
     * @throws ParameterNotFoundException
     */
    private function open(OutputInterface $output, string $service): int
    {
        if (!$this->checkServiceName($service)) {
            $this->error($output, 'Unknown service');
            return -1;
        }
        $command = explode(" ", $this->configHelper->getParameter(sprintf('service.%s.open_command', $service)));
        foreach ($command as $k => $v) {
            $command[$k] = str_replace('\0', ' ', $v);
        }
        return $this->runProcess($command, $output);
    }
}
