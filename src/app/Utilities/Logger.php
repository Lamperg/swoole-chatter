<?php

namespace App\Utilities;

class Logger
{
    const ERROR = 'ERROR';
    const NOTICE = 'NOTICE';

    public static function log(string $message): void
    {
        echo static::renderMessage(static::NOTICE, $message);
    }

    public static function logError(string $message): void
    {
        echo static::renderMessage(static::ERROR, $message);
    }

    protected static function renderMessage(string $level, string $message): string
    {
        $date = new \DateTime();
        return "[{$date->format('Y-m-d H:i:s')}] [$level] $message" . PHP_EOL;
    }
}
