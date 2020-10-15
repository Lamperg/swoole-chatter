<?php

namespace App\Handlers;

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
        Logger::log("message has been received: {$frame->data} (connection: {$frame->fd})");

        // frame data comes in as a string
        $output = json_decode($frame->data, true);

        $message = trim($output['message']);
        $username = trim($output['username']);

        $this->messageRepository->add($username, $message);

        // now we notify all of the connected clients
        foreach ($this->userRepository->getAll() as $user) {
            $message = [
                "text" => $message,
                "client" => $frame->fd,
                "username" => $username
            ];

            $server->push($user->getConnectionId(), json_encode($message));
        }
    }
}
