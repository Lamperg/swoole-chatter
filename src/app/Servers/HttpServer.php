<?php

namespace App\Servers;

use App\RunnerFactory;
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\HTTP\Response;

class HttpServer
{
    protected Server $server;

    public function __construct()
    {
        $this->server = new Server("app", 9000, SWOOLE_BASE);

        $this->server->on("request", function (Request $request, Response $response) {
            $app = RunnerFactory::create();

            $response->header("Content-Type", "application/json");
            $response->end(json_encode($app->run()));
        });

        $this->server->start();
    }
}
