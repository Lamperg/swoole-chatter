<?php

namespace App;

use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;

class Router
{
    protected Dispatcher $dispatcher;

    public function __construct()
    {
        $this->dispatcher = simpleDispatcher(new RoutesCollection());
    }

    /**
     * Handles request by provided method and URI params.
     *
     * @param string $method
     * @param string $uri
     * @return array|mixed
     */
    public function handleRequest(string $method, string $uri)
    {
        list($code, $handler, $vars) = $this->dispatcher->dispatch($method, $uri);

        switch ($code) {
            case Dispatcher::NOT_FOUND:
                return [
                    'status' => 404,
                    'message' => 'Not Found',
                    'errors' => [sprintf('The URI "%s" was not found', $uri)]
                ];
            case Dispatcher::METHOD_NOT_ALLOWED:
                return [
                    'status' => 405,
                    'message' => 'Method Not Allowed',
                    'errors' => [sprintf('Method "%s" is not allowed', $method)]
                ];
            case Dispatcher::FOUND:
                return call_user_func($handler, $vars);
        }
    }
}
