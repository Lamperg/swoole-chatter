<?php

namespace App\Responses;

class ErrorResponse extends JsonResponse
{
    public const LOGIN_ERROR = "login_error";
    public const GENERAL_ERROR = "general_error";

    protected string $message;
    protected string $errorType;

    public function __construct(string $message, string $errorType = null)
    {
        $this->message = $message;
        $this->errorType = $errorType ?? static::GENERAL_ERROR;
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
        return [
            "message" => $this->message,
            "error_type" => $this->errorType,
        ];
    }
}
