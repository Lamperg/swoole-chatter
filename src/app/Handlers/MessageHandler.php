<?php

namespace App\Handlers;

use App\Models\Message;
use App\Repositories\MessageRepository;
use App\Repositories\UserRepository;
use App\Utilities\Logger;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

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

            // now we notify all of the connected clients
            foreach ($this->userRepository->getAll() as $user) {
                $message = [
                    "text" => $message,
                    "username" => $username,
                    "client" => $connectionId,
                    "id" => $messageModel->getId()
                ];

                $server->push($user->getConnectionId(), json_encode($message));
            }
        } catch (\Exception $e) {
            Logger::logError($e->getMessage());
            $server->push($connectionId, $e->getMessage());
        }
    }
}
