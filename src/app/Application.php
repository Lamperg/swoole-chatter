<?php

namespace App;

use \App\Kernel\WebsocketServer;

class Application
{
    protected static WebsocketServer $server;

    protected function __construct()
    {
    }

    public static function getServer(): WebsocketServer
    {
        if (empty(static::$server)) {
            static::$server = new WebsocketServer(new Router(), new DataSource());
        }

        return static::$server;
    }
}
