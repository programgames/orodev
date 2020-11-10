<?php

namespace Programgames\OroDev\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelpCommand extends Command
{
    public static $defaultName = 'help';

    protected function configure()
    {
        $this->setDescription('Display help');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hello');

        return 0;
    }
}
