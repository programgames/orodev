<?php

namespace Programgames\OroDev\Tools\DaemonChecker;

use Programgames\OroDev\Exception\DaemonNotRunning;
use RuntimeException;
use Symfony\Component\Process\Process;

class MailcatcherDaemonChecker implements DaemonCheckerInterface, WebInterfaceInterface
{
    public function isDaemonRunning(): bool
    {
        $process = new Process(['ps', 'aux']);
        $process->run();
        if (!$process->isSuccessful()) {
            return false;
        }
        $running = preg_match('/.*mailcatcher.*/', $process->getOutput(), $matches);
        if (!$running) {
            return false;
        }
        return true;
    }

    public function getRunningPort(): int
    {
        $process = new Process(['lsof', '-Pni4']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('Failed to check "%s" daemon. %s, command not found', 'mailcatcher', $process->getErrorOutput())
            );
        }
        $lsofOutput = $process->getOutput();
        preg_match('/.*ruby.*/', $lsofOutput, $matches);
        $pid = $this->getPid();

        $lsofPort = null;
        foreach ($matches as $match) {
            if (preg_match('/.*' . $pid . '.*/', $match, $matchesPid)) {
                $pieces = explode(' ', preg_replace('/\s+/', ' ', reset($matchesPid)));
                $lsofPort = preg_replace('/.*:/', '', $pieces[8]);
            }
        }
        if ($lsofPort === null) {
            throw new RuntimeException("Mailcatcher port not found");
        }
        return $lsofPort;
    }

    public function getPid(): int
    {
        $process = new Process(['ps', 'aux']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('Failed to check "%s" PID. %s, command not found', 'mailcatcher', $process->getErrorOutput())
            );
        }
        $lsofOutput = $process->getOutput();
        preg_match('/.*ruby.*mailcatcher.*/', $lsofOutput, $matches);
        $mailcatcherProcess = preg_replace('/\s+/', ' ', reset($matches));

        if (empty($mailcatcherProcess)) {
            throw new DaemonNotRunning('Mailcatcher is not running');
        }

        $pieces = explode(' ', $mailcatcherProcess);

        return $pieces[1];
    }

    public function getWebInterfacePort(): int
    {
        $pid = $this->getPid();

        $process = new Process(['lsof', '-aPi', '-p', $pid]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('Failed to use "%s" program. %s, command not found', 'lsof -n -i', $process->getErrorOutput())
            );
        }

        preg_match_all('/.*ruby.*/', $process->getOutput(), $matches);

        $pieces = array_filter(explode(' ', reset($matches)[1]));

        return (int) filter_var($pieces[20], FILTER_SANITIZE_NUMBER_INT);
    }
}
