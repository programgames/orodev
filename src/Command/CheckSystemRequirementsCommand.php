<?php

namespace Programgames\OroDev\Command;

use MCStreetguy\ComposerParser\ComposerJson;
use Programgames\OroDev\Requirements\RenderTableTrait;
use Programgames\OroDev\Requirements\System\OroCommerce3EESystemRequirements;
use Programgames\OroDev\Requirements\System\OroCommerce4EESystemRequirements;
use Programgames\OroDev\Requirements\System\OroPlatform4CESystemRequirements;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Requirements\RequirementCollection;

class CheckSystemRequirementsCommand extends Command
{
    public static $defaultName = 'check:system';

    use RenderTableTrait;

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
            ->addOption('env', null, InputOption::VALUE_OPTIONAL, 'environment of the application', 'dev')
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Check system requirements');

        $oroSystemRequirements = $this->getOroSystemRequirements($input);
        $this->renderTable($oroSystemRequirements->getRequirements(), 'Optional recommendations', $output);

        $exitCode = 0;
        $numberOfFailedRequirements = count(
            $oroSystemRequirements->getFailedRequirements()
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
     * @param InputInterface $input
     *
     * @return RequirementCollection
     */
    protected function getOroSystemRequirements(InputInterface $input)
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
                return new OroCommerce4EESystemRequirements($input->getOption('env'));
            } else {
                if (preg_match('/3./', $version)) {
                    return new OroCommerce3EESystemRequirements($input->getOption('env'));
                } else {
                    throw new RuntimeException('Application version not supported');
                }
            }
        } elseif (array_key_exists('oro/commerce', $require)) {
            $version = $require['oro/commerce'];
            if (preg_match('/4./', $version)) {
                return new OroPlatform4CESystemRequirements($input->getOption('env'));
            } else {
                if (preg_match('/3./', $version)) {
                    throw new RuntimeException('Not supported yet');
                } else {
                    throw new RuntimeException('Application version not supported');
                }
            }
        } elseif (array_key_exists('oro/platform', $require)) {
            $version = $require['oro/platform'];
            if (preg_match('/4./', $version)) {
                return new OroPlatform4CESystemRequirements($input->getOption('env'));
            } else {
                if (preg_match('/3./', $version)) {
                    throw new RuntimeException('Not supported yet');
                } else {
                    throw new RuntimeException('Application version not supported');
                }
            }
        }
        throw new RuntimeException('Application not supported');
    }
}
