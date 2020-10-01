<?php

namespace App;

class Runner
{
    public const VERSION = "0.0.3";

    public function run()
    {
        return "Hello from version: " . self::VERSION;
    }
}
