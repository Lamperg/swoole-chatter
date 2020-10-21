<?php

namespace Tests;

use App\Models\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    /**
    * @test
    */
    public function messageCanBeCreated()
    {
        $username = "test";
        $text = "hello world";
        $message = new Message($username, $text);

        $this->assertEquals($text, $message->getText());
        $this->assertEquals($username, $message->getUsername());
    }
}
