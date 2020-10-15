<?php

namespace App\Models;

class User
{
    protected string $username;
    protected int $connectionId;

    public function __construct(string $username, int $connectionId)
    {
        $this->username = $username;
        $this->connectionId = $connectionId;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getConnectionId(): int
    {
        return $this->connectionId;
    }
}
