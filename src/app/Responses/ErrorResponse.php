<?php

namespace App\Responses;

class ErrorResponse extends JsonResponse
{
    protected string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * {@inheritDoc}
     */
    protected function getType(): string
    {
        return 'error';
    }

    /**
     * {@inheritDoc}
     */
    protected function getBody()
    {
        return ['message' => $this->message];
    }
}
