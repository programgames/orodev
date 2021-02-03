<?php

namespace Programgames\OroDev\Command;

use MCStreetguy\ComposerParser\ComposerJson;
use Programgames\OroDev\Requirements\Application\OroApplicationRequirementsInterface;
use Programgames\OroDev\Requirements\Application\OroCommerce3CEApplicationRequirements;
use Programgames\OroDev\Requirements\Application\OroCommerce3EEApplicationRequirements;
use Programgames\OroDev\Requirements\Application\OroPlatform3CEApplicationRequirements;
use Programgames\OroDev\Requirements\Application\OroPlatform4CEApplicationRequirements;
use Programgames\OroDev\Requirements\Application\OroPlatform4EEApplicationRequirements;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Requirements\Requirement;

class CheckOroRequirementsCommand extends Command
{
    public static $defaultName = 'check:oro';

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
            ->setDescription('Checks that the application meets the system requirements.')
            ->addOption('env', null, InputOption::VALUE_OPTIONAL, 'environment of the application', 'dev')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command checks that the application meets the system requirements.

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
        $output->writeln('Check application requirements');

        $oroRequirements = $this->getOroRequirements($input);
        $this->renderTable($oroRequirements->getMandatoryRequirements(), 'Mandatory requirements', $output);
        $this->renderTable($oroRequirements->getPhpIniRequirements(), 'PHP settings', $output);
        $this->renderTable($oroRequirements->getOroRequirements(), 'Oro specific requirements', $output);
        $this->renderTable($oroRequirements->getRecommendations(), 'Optional recommendations', $output);

        $exitCode = 0;
        $numberOfFailedRequirements = count(
            $oroRequirements->getFailedRequirements()
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
     * @return OroApplicationRequirementsInterface
     * @throws \Exception
     */
    protected function getOroRequirements(InputInterface $input)
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
                return new OroPlatform4EEApplicationRequirements($input->getOption('env'));
            } else {
                if (preg_match('/3./', $version)) {
                    return new OroCommerce3EEApplicationRequirements($input->getOption('env'));
                } else {
                    throw new RuntimeException('Application version not supported');
                }
            }
        } elseif (array_key_exists('oro/commerce', $require)) {
            $version = $require['oro/commerce'];
            if (preg_match('/4./', $version)) {
                return new OroPlatform4CEApplicationRequirements($input->getOption('env'));
            } else {
                if (preg_match('/3./', $version)) {
                    return new OroCommerce3CEApplicationRequirements($input->getOption('env'));
                } else {
                    throw new RuntimeException('Application version not supported');
                }
            }
        } elseif (array_key_exists('oro/platform', $require)) {
            $version = $require['oro/platform'];
            if (preg_match('/4./', $version)) {
                return new OroPlatform4CEApplicationRequirements($input->getOption('env'));
            } else {
                if (preg_match('/3./', $version)) {
                    return new OroPlatform3CEApplicationRequirements($input->getOption('env'));
                } else {
                    throw new RuntimeException('Application version not supported');
                }
            }
        }
        throw new RuntimeException('Application not supported');
    }
}
