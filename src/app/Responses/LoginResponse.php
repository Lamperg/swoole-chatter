<?php

namespace App\Responses;

class LoginResponse extends JsonResponse
{
    protected string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * {@inheritDoc}
     */
    protected function getBody()
    {
        return $this->message;
    }

    /**
     * {@inheritDoc}
     */
    protected function getType(): string
    {
        return "login";
    }
}
