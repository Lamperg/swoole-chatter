<?php

require_once 'vendor/autoload.php';

$server = new Swoole\Http\Server("app", 9000, SWOOLE_BASE);

$server->on("request", function (Swoole\Http\Request $request, Swoole\HTTP\Response $response) {
    $app = App\RunnerFactory::create();

    $response->header("Content-Type", "application/json");
    $response->end(json_encode($app->run()));
});

$server->start();
