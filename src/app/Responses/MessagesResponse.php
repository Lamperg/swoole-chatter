<?php

namespace App\Responses;

use App\Models\Message;

class MessagesResponse extends JsonResponse
{
    protected array $messages = [];

    public function __construct(array $collection)
    {
        foreach ($collection as $message) {
            if ($message instanceof Message) {
                $this->messages[] = $message->toArray();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getBody()
    {
        return $this->messages ?? [];
    }

    /**
     * {@inheritDoc}
     */
    protected function getType(): string
    {
        return "messages";
    }
}
