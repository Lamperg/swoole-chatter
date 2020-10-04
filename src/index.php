<?php

require_once 'vendor/autoload.php';

$server = new Swoole\Http\Server("app", 9000, SWOOLE_BASE);

$server->on("request", function (Swoole\Http\Request $request, Swoole\HTTP\Response $response) {
    $app = App\RunnerFactory::create();
    $app->run();

    $response->header("Content-Type", "text/plain");
    $response->end($app->run());
});

$server->start();
