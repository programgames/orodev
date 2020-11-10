<?php

namespace Programgames\OroDev\Command;

use Programgames\OroDev\Requirements\OroCommerce4SystemRequirements;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Requirements\Requirement;

class CheckSystemRequirementsCommand extends Command
{
    public static $defaultName = 'check:system';

    /**
     * CheckRequirementsCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Checks that the system meets the requirements.')
            ->addOption('env', null, InputOption::VALUE_OPTIONAL, 'environment of the application', 'dev',)
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Check system requirements');

        $oroDevRequirements = $this->getOroCommerce4SystemRequirements($input);
        $this->renderTable($oroDevRequirements->getRequirements(), 'Optional recommendations', $output);

        $exitCode = 0;
        $numberOfFailedRequirements = count(
            $oroDevRequirements->getFailedRequirements()
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
     * @param Requirement[] $requirements
     * @param string $header
     * @param OutputInterface $output
     */
    protected function renderTable(array $requirements, $header, OutputInterface $output)
    {
        $rows = [];
        $verbosity = $output->getVerbosity();
        foreach ($requirements as $requirement) {
            if ($requirement->isFulfilled()) {
                if ($verbosity >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                    $rows[] = ['OK', $requirement->getTestMessage()];
                }
            } elseif ($requirement->isOptional()) {
                if ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                    $rows[] = ['WARNING', $requirement->getHelpText()];
                }
            } else {
                if ($verbosity >= OutputInterface::VERBOSITY_NORMAL) {
                    $rows[] = ['ERROR', $requirement->getHelpText()];
                }
            }
        }

        if (!empty($rows)) {
            $table = new Table($output);
            $table
                ->setHeaders(['Check  ', $header])
                ->setRows([]);
            foreach ($rows as $row) {
                $table->addRow($row);
            }
            $table->render();
        }
    }

    /**
     * @param InputInterface $input
     *
     * @return OroCommerce4SystemRequirements
     */
    protected function getOroCommerce4SystemRequirements(InputInterface $input)
    {
        return new OroCommerce4SystemRequirements($input->getOption('env'));
    }
}
