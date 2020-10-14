<?php

namespace App;

use \App\Kernel\WebsocketServer;

class Application
{
    protected WebsocketServer $server;

    public function __construct()
    {
        $this->server = new WebsocketServer();
    }
}
