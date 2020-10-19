<?php

namespace App\Responses;

class LoginResponse extends JsonResponse
{
    protected string $message;

    public function __construct(string $username)
    {
        $this->message = "user $username successfully logged in";
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
