<?php

namespace App\Utilities;

class Logger
{
    const ERROR = 'ERROR';
    const NOTICE = 'NOTICE';

    /**
     * Logs notice level messages.
     *
     * @param string $message
     */
    public static function log(string $message): void
    {
        echo static::renderMessage(static::NOTICE, $message);
    }

    /**
     * Logs error level messages.
     *
     * @param string $message
     */
    public static function logError(string $message): void
    {
        echo static::renderMessage(static::ERROR, $message);
    }

    /**
     * Retrieves formatted message for provided log level.
     *
     * @param string $level
     * @param string $message
     * @return string
     */
    protected static function renderMessage(string $level, string $message): string
    {
        $date = new \DateTime();
        $formattedTime = TimeHelper::format($date);

        return "[$formattedTime] [$level] $message" . PHP_EOL;
    }
}
