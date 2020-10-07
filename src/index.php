<?php

require_once 'vendor/autoload.php';

$router = new \App\Router();
$server = new \App\WebsocketServer($router);
