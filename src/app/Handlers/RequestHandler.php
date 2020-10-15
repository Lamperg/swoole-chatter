<?php

namespace App\Handlers;

use App\Router;
use Swoole\Http\Request;
use Swoole\Http\Response;

class RequestHandler
{
    protected Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function __invoke(Request $request, Response $response): void
    {
        $uri = $request->server['request_uri'];
        $method = $request->server['request_method'];

        // populate the global state with the request info
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REMOTE_ADDR'] = $request->server['remote_addr'];

        $_GET = $request->get ?? [];
        $_FILES = $request->files ?? [];

        // form-data and x-www-form-urlencoded work out of the box so we handle JSON POST here
        if ($method === 'POST' && $request->header['content-type'] === 'application/json') {
            $body = $request->rawContent();
            $_POST = empty($body) ? [] : json_decode($body);
        } else {
            $_POST = $request->post ?? [];
        }

        // global content type for our responses
        $response->header('Content-Type', 'application/json');

        $result = $this->router->handleRequest($method, $uri);
        // write the JSON string out
        $response->end(json_encode($result));
    }
}
