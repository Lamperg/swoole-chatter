<?php

namespace App\Handlers;

use App\Models\User;
use App\Models\Message;
use App\Utilities\Logger;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use App\Utilities\Authenticator;
use App\Responses\ErrorResponse;
use App\Responses\MessagesResponse;
use App\Repositories\UserRepository;
use App\Repositories\MessageRepository;

class MessageHandler
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

    public function __invoke(Server $server, Frame $frame): void
    {
        $connectionId = $frame->fd;
        $data = json_decode($frame->data, true);

        $message = $data['message'] ?? "";
        $username = $data['username'] ?? "";

        try {
            $user = new User($username, $connectionId);

            if (!$this->authenticator->isLoggedIn($user)) {
                throw new \Exception("user '$username' is not logged in");
            }

            $messageModel = new Message($username, $message);

            $this->messageRepository->add($messageModel);
            $messagesResponse = new MessagesResponse([$messageModel]);

            foreach ($this->userRepository->all() as $user) {
                // push new message to all active clients
                go(function () use ($server, $user, $messagesResponse) {
                    $server->push($user->getConnectionId(), $messagesResponse->getJson());
                });
            }

            Logger::log("message has been received: {$frame->data} (connection: $connectionId)");
        } catch (\Exception $e) {
            Logger::logError($e->getMessage());

            $errorResponse = new ErrorResponse($e->getMessage());
            $server->push($connectionId, $errorResponse->getJson());
        }
    }
}
