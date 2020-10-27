<?php

namespace App\Handlers;

use Swoole\Http\Request;
use App\Utilities\Logger;
use Swoole\HTTP\Response;
use App\Responses\ErrorResponse;
use App\Responses\MessagesResponse;
use App\Repositories\MessageRepository;

class RequestHandler
{
    protected MessageRepository $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    public function __invoke(Request $request, Response $response): void
    {
        try {
            $messages = $this->messageRepository->all();
            $jsonResponse = new MessagesResponse($messages);
        } catch (\Exception $e) {
            Logger::logError($e->getMessage());
            $jsonResponse = new ErrorResponse($e->getMessage());
        }

        $response->header("Content-Type", "application/json");
        $response->end($jsonResponse->render());
    }
}
