<?php

namespace App\Handlers;

use App\Models\User;
use App\Responses\ErrorResponse;
use App\Responses\UsersResponse;
use Swoole\Http\Request;
use App\Utilities\Logger;
use Swoole\WebSocket\Server;
use Swoole\Coroutine\WaitGroup;
use App\Responses\MessagesResponse;
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
        $connectionId = $request->fd;
        Logger::log("connection {$request->fd} has been opened (workerId: {$server->getWorkerId()})");

        $users = [];
        $messages = [];

        try {
            $user = new User('test_user_tmp', $connectionId);

            $this->userRepository->add($user);

            $wg = new WaitGroup();

            go(function () use ($wg, &$users) {
                $wg->add();
                $users = $this->userRepository->all();
                $wg->done();
            });

            go(function () use ($wg, &$messages) {
                $wg->add();
                $messages = $this->messageRepository->all();
                $wg->done();
            });

            $wg->wait();

            $messagesResponse = new MessagesResponse($messages);
            // push existed messages to opened connection
            $server->push($connectionId, $messagesResponse->getJson());

            $usersResponse = new UsersResponse($users);

            foreach ($users as $user) {
                // push online users to all active connections
                go(function () use ($server, $user, $usersResponse) {
                    $server->push($user->getConnectionId(), $usersResponse->getJson());
                });
            }
        } catch (\Exception $e) {
            Logger::logError($e->getMessage());

            $errorResponse = new ErrorResponse($e->getMessage());
            $server->push($connectionId, $errorResponse->getJson());
        }
    }
}
