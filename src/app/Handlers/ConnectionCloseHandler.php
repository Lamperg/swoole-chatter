<?php

namespace App\Handlers;

use App\Responses\UsersResponse;
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

            $users = $this->userRepository->all();
            $usersResponse = new UsersResponse($users);

            foreach ($users as $user) {
                // push updated online users to all active connections
                go(function () use ($server, $user, $usersResponse) {
                    $server->push((string)$user->getConnectionId(), $usersResponse->render());
                });
            }
        } catch (\Exception $e) {
            Logger::logError($e->getMessage());
        }

        Logger::log("connection {$connection} has been closed (workerId: {$server->getWorkerId()})");
    }
}
