<?php

namespace App\Handlers;

use App\Models\User;
use Swoole\Http\Request;
use App\Utilities\Logger;
use Swoole\WebSocket\Server;
use Swoole\Coroutine\WaitGroup;
use App\Responses\ErrorResponse;
use App\Responses\UsersResponse;
use App\Utilities\Authenticator;
use App\Responses\MessagesResponse;
use App\Repositories\UserRepository;
use App\Repositories\MessageRepository;

class ConnectionOpenHandler
{
    protected Authenticator $authenticator;
    protected UserRepository $userRepository;
    protected MessageRepository $messageRepository;

    public function __construct(
        Authenticator $authenticator,
        UserRepository $userRepository,
        MessageRepository $messageRepository
    ) {
        $this->authenticator = $authenticator;
        $this->userRepository = $userRepository;
        $this->messageRepository = $messageRepository;
    }

    public function __invoke(Server $server, Request $request): void
    {
        $connectionId = $request->fd;
        Logger::log("connection {$request->fd} has been opened (workerId: {$server->getWorkerId()})");

        $username = $request->get["username"];

        $users = [];
        $messages = [];

        try {
            if ($this->authenticator->isUsernameUsed($username)) {
                throw new \InvalidArgumentException("username '$username' has already been used");
            }

            $this->authenticator->login(new User($username, $connectionId));

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
                // push updated online users to all active connections
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
