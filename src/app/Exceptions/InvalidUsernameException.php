<?php

namespace App\Exceptions;

class InvalidUsernameException extends \Exception
{
    public function __construct(string $username)
    {
        parent::__construct();
        $this->message = "username '$username' has already been used";
    }
}
