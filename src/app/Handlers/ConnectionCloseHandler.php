<?php

namespace App\Handlers;

use App\Utilities\Logger;
use Swoole\WebSocket\Server;
use App\Repositories\UserRepository;

class ConnectionCloseHandler
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(Server $server, int $connection): void
    {
        try {
            // remove online user connection
            $this->userRepository->delete($connection);
        } catch (\Exception $e) {
            Logger::logError($e->getMessage());
        }

        Logger::log("connection {$connection} has been closed (workerId: {$server->getWorkerId()})");
    }
}
