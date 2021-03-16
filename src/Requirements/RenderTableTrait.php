<?php

namespace Programgames\OroDev\Requirements;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Requirements\Requirement;

trait RenderTableTrait
{
    /**
     * @param Requirement[] $requirements
     * @param string $header
     * @param OutputInterface $output
     */
    protected function renderTable(array $requirements, string $header, OutputInterface $output): void
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
            } elseif ($verbosity >= OutputInterface::VERBOSITY_NORMAL) {
                $rows[] = ['ERROR', $requirement->getHelpText()];
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
}
