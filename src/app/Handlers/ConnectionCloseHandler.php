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

    public function __invoke(Server $server, int $client): void
    {
        // remove the client from the memory table
        $this->userRepository->getUsers()->del($client);

        Logger::log("connection {$client} has been closed (workerId: {$server->getWorkerId()})");
    }
}
