<?php

namespace App;

class RunnerFactory
{
    public static function create()
    {
        return new Runner();
    }
}
