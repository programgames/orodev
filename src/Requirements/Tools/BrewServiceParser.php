<?php

namespace Programgames\OroDev\Requirements\Tools;

class BrewServiceParser
{
    public const STATUS_RUNNING = 'started';
    public const STATUS_STOPPED = 'stopped';
    public const STATUS_ERROR = 'error';

    public static function isServiceRunning($processRow)
    {
        return preg_match('/.*' . self::STATUS_RUNNING . '.*/', $processRow, $matches);
    }
}
