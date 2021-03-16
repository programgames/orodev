<?php

namespace Programgames\OroDev\Command;

use MCStreetguy\ComposerParser\ComposerJson;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class SwitchEnvironmentCommand extends ColoredCommand
{
    public static $defaultName = 'switch';

    public const ORO3CE = 'ORO3CE';
    public const ORO4CE = 'ORO4CE';
    public const ORO3EE = 'ORO3EE';
    public const ORO4EE = 'ORO4EE';

    public const PHP72 = '7.2';
    public const PHP74 = '7.4';

    public const ELASTICSEARCH7 = 'es7';
    public const ELASTICSEARCH6 = 'es6';

    protected function configure(): void
    {
        $this
            ->setDescription('Switch environment corresponding to current application')
            ->addOption('env', null, InputOption::VALUE_OPTIONAL, 'environment of the application', 'dev')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command automatically switch environment corresponding to current application.
EOT
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $version = $this->getOroVersion();

        switch ($version) {
            case self::ORO3CE:
                $this->switchToOro3CE($output);
                break;
            case self::ORO3EE:
                $this->switchToOro3EE($output);
                break;
            case self::ORO4CE:
                $this->switchToOro4CE($output);
                break;
            case self::ORO4EE:
                $this->switchToOro4EE($output);
                break;
        }

        return 0;
    }

    private function getOroVersion(): string
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
                return self::ORO4EE;
            }

            return self::ORO3EE;
        }

        $version = $require['oro/commerce'];

        if (preg_match('/3./', $version)) {
            return self::ORO3CE;
        }

        return self::ORO4CE;
    }

    private function changePhpTo(string $version, OutputInterface $output): void
    {
        preg_match("#^\d.\d#", PHP_VERSION, $matches);

        $phpVersion = 'php@' . $matches[0];

        $process = new Process(['brew', 'unlink', $phpVersion]);
        $process->run();
        $this->info($output, sprintf('Unlinking %s',$phpVersion));
        if (!$process->isSuccessful()) {
            throw new RuntimeException('An error occurred while unlinking the current php version');
        }

        $process = new Process(['brew', 'link', sprintf('php@%s', $version)]);
        $process->run();
        $this->info($output, sprintf('Linking php %s',$version));
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('An error occurred while linking the php %s version with brew', $version)
            );
        }
    }

    private function switchToOro3EE(OutputInterface $output): void
    {
        $this->changePhpTo(self::PHP72, $output);
        $this->changeElasticSearchTo(self::ELASTICSEARCH6, $output);
    }

    private function switchToOro4EE(OutputInterface $output): void
    {
        $this->changePhpTo(self::PHP74, $output);
        $this->changeElasticSearchTo(self::ELASTICSEARCH7, $output);
    }

    private function switchToOro3CE(OutputInterface $output): void
    {
        $this->changePhpTo(self::PHP72,$output);
    }

    private function switchToOro4CE(OutputInterface $output): void
    {
        $this->changePhpTo(self::PHP74,$output);
    }

    private function changeElasticSearchTo(string $version, OutputInterface $output): void
    {
        $process = new Process(['brew', 'unlink', 'elasticsearch@6']);
        $process->run();
        $this->info($output, sprintf('Unlinking elasticsearch %s',6));
        if (!$process->isSuccessful()) {
            throw new RuntimeException('An error occurred while unlinking elasticsearch 6');
        }

        $process = new Process(['brew', 'unlink', 'elastic/tap/elasticsearch-full']);
        $process->run();
        $this->info($output, sprintf('Unlinking elasticsearch %s',7));
        if (!$process->isSuccessful()) {
            throw new RuntimeException('An error occurred while unlinking elasticsearch 7');
        }

        if ($version === self::ELASTICSEARCH7) {
            $process = new Process(['brew', 'link','elastic/tap/elasticsearch-full']);
            $this->info($output, sprintf('Linking elasticsearch %s',7));
        } else {
            $process = new Process(['brew', 'link','elasticsearch@6']);
            $this->info($output, sprintf('Linking elasticsearch %s',6));
        }

        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('An error occurred while linking the elasticsearch %s version with brew', $version)
            );
        }
    }
}
