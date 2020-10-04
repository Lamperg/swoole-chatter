<?php

require_once 'vendor/autoload.php';

$server = new swoole_http_server("app", 9000, SWOOLE_BASE);

$server->on("request", function ($request, $response) {
    $app = App\RunnerFactory::create();
    $app->run();

    $response->header("Content-Type", "text/plain");
    $response->end($app->run());
});

$server->start();
