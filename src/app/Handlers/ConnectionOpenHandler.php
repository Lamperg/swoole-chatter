<?php

namespace App\Handlers;

use App\Models\User;
use App\Responses\LoginResponse;
use Swoole\Http\Request;
use App\Utilities\Logger;
use App\Utilities\Purifier;
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
    protected Purifier $purifier;
    protected Authenticator $authenticator;
    protected UserRepository $userRepository;
    protected MessageRepository $messageRepository;

    public function __construct(
        Authenticator $authenticator,
        UserRepository $userRepository,
        MessageRepository $messageRepository
    ) {
        $this->purifier = new Purifier();
        $this->authenticator = $authenticator;
        $this->userRepository = $userRepository;
        $this->messageRepository = $messageRepository;
    }

    public function __invoke(Server $server, Request $request): void
    {
        $users = [];
        $messages = [];
        $connectionId = $request->fd;
        $username = $request->get["username"] ?? "";
        Logger::log("connection $connectionId has been opened (workerId: {$server->getWorkerId()})");

        try {
            $username = $this->purifier->purify($username);

            if ($this->authenticator->isUsernameUsed($username)) {
                throw new \InvalidArgumentException("username '$username' has already been used");
            }

            $this->authenticator->login(new User($username, $connectionId));

            go(function () use ($server, $connectionId, $username) {
                $loginResponse = new LoginResponse("user $username successfully logged in");
                $server->push($connectionId, $loginResponse->render());
            });

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
            $server->push($connectionId, $messagesResponse->render());

            $usersResponse = new UsersResponse($users);

            foreach ($users as $user) {
                // push updated online users to all active connections
                go(function () use ($server, $user, $usersResponse) {
                    $server->push($user->getConnectionId(), $usersResponse->render());
                });
            }
        } catch (\Exception $e) {
            Logger::logError($e->getMessage());

            $errorResponse = new ErrorResponse($e->getMessage());
            $server->push($connectionId, $errorResponse->render());
        }
    }
}
