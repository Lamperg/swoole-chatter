<?php

namespace App\Models;

use DateTime;

class Message
{
    protected int $id;
    protected string $text;
    protected string $username;
    protected DateTime $date;

    public function __construct(int $id, string $username, string $text, DateTime $date)
    {
        $this->id = $id;
        $this->text = $text;
        $this->date = $date;
        $this->username = $username;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }
}
