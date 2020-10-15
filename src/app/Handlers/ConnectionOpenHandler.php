<?php

namespace App\Handlers;

use App\Models\User;
use App\Repositories\MessageRepository;
use App\Repositories\UserRepository;
use App\Utilities\Logger;
use Swoole\Http\Request;
use Swoole\WebSocket\Server;

class ConnectionOpenHandler
{
    protected UserRepository $userRepository;
    protected MessageRepository $messageRepository;

    public function __construct(UserRepository $userRepository, MessageRepository $messageRepository)
    {
        $this->userRepository = $userRepository;
        $this->messageRepository = $messageRepository;
    }

    public function __invoke(Server $server, Request $request): void
    {
        Logger::log("connection {$request->fd} has been opened (workerId: {$server->getWorkerId()})");

        $user = new User('test_user_tmp', $request->fd);
        // store the user in our memory table
        $this->userRepository->add($user);

        // update all the client with the existing messages
        foreach ($this->messageRepository->getAll() as $message) {
            $server->push($request->fd, json_encode($message));
        }
    }
}
