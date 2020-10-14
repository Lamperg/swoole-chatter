<?php

namespace App\Utilities;

class Logger
{
    public static function log(string $message): void
    {
        echo $message . PHP_EOL;
    }
}
