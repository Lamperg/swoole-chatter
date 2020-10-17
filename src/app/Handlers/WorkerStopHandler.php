<?php

namespace App\Handlers;

use App\Utilities\Logger;
use Swoole\WebSocket\Server;

class WorkerStopHandler
{
    public function __invoke(Server $server, int $workerId): void
    {
        Logger::log("worker $workerId: has been stopped");
    }
}
