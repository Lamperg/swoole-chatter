<?php

require_once 'vendor/autoload.php';

$http = new swoole_http_server("app", 9000);

$http->on("request", function ($request, $response) {
    $app = App\RunnerFactory::create();
    $app->run();

    $response->header("Content-Type", "text/plain");
    $response->end($app->run());
});

$http->start();
