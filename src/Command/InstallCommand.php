<?php

declare(strict_types=1);

namespace Programgames\OroDev\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class InstallCommand extends ColoredCommand
{
    public static $defaultName = 'install';

    private $servicesCaches = [];
    protected function configure(): void
    {
        $this
            ->setDescription('Install every necessary tools on the system')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command automatically install every necessary tools on the system.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->info($output,'test apps');

        foreach ($this->getNecessaryCommands() as $tool) {
            $this->commandExist($tool,$output);
        }

        foreach ($this->getNecessaryServices() as $tool) {
            $this->serviceExist($tool,$output);
        }

        return 0;
    }

    protected function commandExist(string $command, OutputInterface $output): bool
    {
        $this->info($output, sprintf('Testing if command : %s is installed', $command));
        $process = new Process(['which', $command]);
        $process->run();
        if (!$process->isSuccessful()) {
            $this->error($output, sprintf('Please install %s', $command));
            return false;
        }

        return true;
    }

    protected function serviceExist(string $service, OutputInterface $output)
    {
        $this->info($output, sprintf('Testing if service : %s is installed', $service));

        if (empty($this->servicesCaches)) {
            $process = new Process(['brew','list']);
            $process->run();
            $this->servicesCaches = $process->getOutput();
        }


        preg_match('/' . $service . '/', $this->servicesCaches, $matches);

        if (empty($matches)) {
            $this->error($output, sprintf('Please install %s service', $service));
        }
    }

    private function getNecessaryCommands(): array
    {
        return [
            'brew',
            'ls',
            'postgres',
            'tail',
            'mailcatcher',
            'rabbitmqctl',
            'kibana',
            'which',
            'lsof',
            'launchctl',
            'ps',
            'kill',
            'node'
        ];
    }

    private function getNecessaryServices(): array
    {
        return [
            'elasticsearch@6',
            'elasticsearch-full',
            'postgresql@9.6',
            'rabbitmq',
            'php@7.4',
            'php@7.2'
        ];
    }
}
