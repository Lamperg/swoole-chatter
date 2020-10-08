<?php

require_once 'vendor/autoload.php';

$router = new \App\Router();
$dataSource = new \App\DataSource();
$server = new \App\WebsocketServer($router, $dataSource);
