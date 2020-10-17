<?php

namespace App\Handlers;

use App\Utilities\Logger;
use Swoole\WebSocket\Server;

class WorkerStartHandler
{
    /**
     * Delay in milliseconds
     */
    const PING_DELAY = 25000;

    public function __invoke(Server $server, int $workerId): void
    {
        Logger::log("worker $workerId: has been started");

        $server->tick(static::PING_DELAY, function () use ($server, $workerId) {
            foreach ($server->connections as $id) {
                // we ping all connections to keep them alive
                $server->push($id, 'ping', WEBSOCKET_OPCODE_PING);
            }
        });
    }
}
