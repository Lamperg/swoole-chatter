<?php

namespace App\Servers;

use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class WebsocketServer
{
    protected Server $server;

    public function __construct()
    {
        $this->server = new Server("app", 9000);

        $this->server->on('open', function (Server $server, Request $request): void {
            $this->onConnection($request);
        });
        $this->server->on('message', function (Server $server, Frame $frame): void {
            $this->onMessage($frame);
        });
        $this->server->on('close', function (Server $server, int $fd): void {
            $this->onClose($fd);
        });

        $this->server->on('workerStart', function (Server $server) {
            $this->onWorkerStart($server);
        });

        $this->server->start();
    }

    private function onWorkerStart(Server $server): void
    {
        echo "Worker has been started\n";
    }

    private function onConnection(Request $request): void
    {
        echo "client-{$request->fd} is connected\n";
    }

    private function onMessage(Frame $frame): void
    {
        echo "message received: {$frame->data}\n";

        $this->server->push($frame->fd, "Hello client");
    }

    private function onClose(int $server): void
    {
        echo "connection closed: {$server}\n";
    }
}
