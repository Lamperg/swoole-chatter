<?php

namespace App\Handlers;

use App\Models\User;
use App\Models\Message;
use App\Utilities\Logger;
use App\Utilities\Purifier;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use App\Utilities\Authenticator;
use App\Responses\ErrorResponse;
use App\Responses\MessagesResponse;
use App\Repositories\UserRepository;
use App\Repositories\MessageRepository;

class MessageHandler
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

    public function __invoke(Server $server, Frame $frame): void
    {
        $connectionId = $frame->fd;
        Logger::log("message has been received: {$frame->data} (connection: $connectionId)");
        $data = json_decode($frame->data, true);

        $text = $data['message'] ?? "";
        $username = $data['username'] ?? "";

        try {
            $text = $this->purifier->purify($text);
            $username = $this->purifier->purify($username);

            $user = new User($username, $connectionId);

            if (!$this->authenticator->isLoggedIn($user)) {
                throw new \Exception("user '$username' is not logged in");
            }

            $message = new Message($username, $text);
            $this->messageRepository->add($message);
            $messagesResponse = new MessagesResponse([$message]);

            foreach ($this->userRepository->all() as $user) {
                // push new message to all active clients
                go(function () use ($server, $user, $messagesResponse) {
                    $server->push((string)$user->getConnectionId(), $messagesResponse->render());
                });
            }
        } catch (\Exception $e) {
            Logger::logError($e->getMessage());

            $errorResponse = new ErrorResponse($e->getMessage());
            $server->push($connectionId, $errorResponse->render());
        }
    }
}
