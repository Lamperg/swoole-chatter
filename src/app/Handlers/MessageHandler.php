<?php

namespace App\Handlers;

use App\Models\Message;
use App\Responses\MessagesResponse;
use App\Utilities\Logger;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use App\Responses\ErrorResponse;
use App\Repositories\UserRepository;
use App\Repositories\MessageRepository;

class MessageHandler
{
    protected UserRepository $userRepository;
    protected MessageRepository $messageRepository;

    public function __construct(UserRepository $userRepository, MessageRepository $messageRepository)
    {
        $this->userRepository = $userRepository;
        $this->messageRepository = $messageRepository;
    }

    public function __invoke(Server $server, Frame $frame): void
    {
        $connectionId = $frame->fd;
        Logger::log("message has been received: {$frame->data} (connection: $connectionId)");

        // frame data comes in as a string
        $data = json_decode($frame->data, true);

        $message = $data['message'] ?? "";
        $username = $data['username'] ?? "";

        try {
            $messageModel = new Message($username, $message);

            $this->messageRepository->add($messageModel);
            $messagesResponse = new MessagesResponse([$messageModel]);

            foreach ($this->userRepository->all() as $user) {
                // push new message to all active clients
                go(function () use ($server, $user, $messagesResponse) {
                    $server->push($user->getConnectionId(), $messagesResponse->getJson());
                });
            }
        } catch (\Exception $e) {
            Logger::logError($e->getMessage());

            $errorResponse = new ErrorResponse($e->getMessage());
            $server->push($connectionId, $errorResponse->getJson());
        }
    }
}
