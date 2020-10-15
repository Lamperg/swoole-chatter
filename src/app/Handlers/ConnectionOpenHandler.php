<?php

namespace App\Handlers;

use App\Models\User;
use Swoole\Http\Request;
use App\Utilities\Logger;
use Swoole\WebSocket\Server;
use Swoole\Coroutine\WaitGroup;
use App\Repositories\UserRepository;
use App\Repositories\MessageRepository;

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

        $users = [];
        $existedMessages = [];
        $onlineUsersResponse = [];

        $wg = new WaitGroup();
        $user = new User('test_user_tmp', $request->fd);

        go(function () use ($wg, $user) {
            $wg->add();
            $this->userRepository->add($user);
            $wg->done();
        });

        go(function () use ($wg, &$onlineUsersResponse, &$users) {
            $wg->add();
            $users = $this->userRepository->getAll();

            foreach ($users as $user) {
                $onlineUsersResponse[] = [
                    'id' => $user->getConnectionId(),
                    'username' => $user->getUsername(),
                ];
            }

            $wg->done();
        });

        go(function () use ($wg, &$existedMessages) {
            $wg->add();
            $existedMessages = $this->messageRepository->getAll();
            $wg->done();
        });

        $wg->wait();

        foreach ($users as $user) {
            $server->push($user->getConnectionId(), json_encode(['type' => 'users', 'body' => $onlineUsersResponse]));
        }

        $server->push($request->fd, json_encode(['type' => 'messages', 'body' => $existedMessages]));
    }
}
