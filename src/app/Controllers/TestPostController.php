<?php

namespace App\Controllers;

class TestPostController
{
    public function __invoke(array $vars)
    {
        return [
            'status' => 200,
            'message' => 'Hello world from POST controller!',
            'vars' => [
                'vars' => $vars,
                '$_GET' => $_GET,
                '$_POST' => $_POST,
            ],
        ];
    }
}
