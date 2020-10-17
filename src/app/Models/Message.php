<?php

namespace App\Models;

use DateTime;

class Message
{
    protected int $id;
    protected string $text;
    protected string $username;
    protected DateTime $date;

    public function __construct(string $username, string $text)
    {
        $text = trim($text);
        $username = trim($username);

        if (empty($text)) {
            throw new \InvalidArgumentException('message text cannot be empty');
        }
        if (empty($username)) {
            throw new \InvalidArgumentException('message username cannot be empty');
        }

        $this->text = $text;
        $this->username = $username;
        $this->setDate(new DateTime());
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
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

    public function setDate(DateTime $dateTime): void
    {
        $this->date = $dateTime;
    }
}
