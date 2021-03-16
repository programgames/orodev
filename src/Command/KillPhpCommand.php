<?php

namespace Programgames\OroDev\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class KillPhpCommand extends ColoredCommand
{
    public static $defaultName = 'kill';

    protected function configure(): void
    {
        $this
            ->setDescription('Kill php process')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> stop every php process from Brew.

EOT
            );
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $process = new Process(['ps']);
        $process->run();
        if (!$process->isSuccessful()) {
            return -1;
        }
        preg_match('/.*Cellar.*php.*/', $process->getOutput(), $matches);

        foreach ($matches as $match) {
            $process = preg_replace('/\s+/', ' ', $match);

            $pieces = explode(' ', $process);

            $process = new Process(['kill', '-9',reset($pieces)]);
            $process->run();
        }

        return 0;
    }
}
