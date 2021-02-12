<?php

namespace Programgames\OroDev\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ColoredCommand extends Command
{
    protected function error(OutputInterface $output, string $message)
    {
        $output->writeln(sprintf('<error>%s</error>', $message));
    }

    protected function info(OutputInterface $output, string $message)
    {
        $output->writeln(sprintf('<info>%s</info>', $message));
    }

    protected function question(OutputInterface $output, string $message)
    {
        $output->writeln(sprintf('<question>%s</question>', $message));
    }

    protected function comment(OutputInterface $output, string $message)
    {
        $output->writeln(sprintf('<comment>%s</comment>', $message));
    }

    protected function link(OutputInterface $output, string $link, string $message)
    {
        $output->writeln(sprintf('<href=%s>%s</>', $link, $message));
    }
}
