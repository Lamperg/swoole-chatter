<?php

namespace App\Controllers;

class TestGetController
{
    public function __invoke(array $vars)
    {
        return [
            'status' => 200,
            'message' => 'Hello world from GET controller!',
            'vars' => [
                'vars' => $vars,
                '$_GET' => $_GET,
                '$_POST' => $_POST,
            ],
        ];
    }
}
