<?php

namespace App;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use App\Repositories\UserRepository;
use App\Repositories\MessageRepository;

class Application
{
    protected Router $router;
    protected Server $server;
    protected UserRepository $userRepository;

    /**
     * Pool of message repositories.
     *
     * @var MessageRepository[]
     */
    protected array $messageRepositories = [];

    public function __construct()
    {
        $this->router = new Router();
        $this->userRepository = new UserRepository();
        $this->server = new Server("app", getenv('APP_SERVER_PORT'));

        $this->setHandlers();
    }

    public function run()
    {
        $configs = [
//            "worker_num" => swoole_cpu_num() * 2
            "worker_num" => 2
        ];

        $this->server->set($configs);
        $this->server->start();
    }

    protected function setHandlers()
    {
        $this->server->on('workerStart', function (Server $server, int $workerId) {
            echo "worker $workerId: has been started" . PHP_EOL;
            $this->messageRepositories[$workerId] = new MessageRepository();
        });

        $this->server->on('workerStop', function (Server $server, int $workerId) {
            echo "worker $workerId: has been stopped" . PHP_EOL;
            if (isset($this->messageRepositories[$workerId])) {
                unset($this->messageRepositories[$workerId]);
            }
        });

        $this->server->on('open', function (Server $server, Request $request): void {
            echo "connection open: {$request->fd}; workerId: " . $server->getWorkerId() . PHP_EOL;

            $messageRepository = $this->messageRepositories[$server->getWorkerId()];

            // store the client on our memory table
            $this->userRepository->getUsers()->set($request->fd, ['user' => $request->fd]);

            // update all the client with the existing messages
            foreach ($messageRepository->getAll() as $message) {
                $server->push($request->fd, json_encode($message));
            }
        });

        $this->server->on('close', function (Server $server, int $client): void {
            echo "client {$client} closed" . PHP_EOL;
            // remove the client from the memory table
            $this->userRepository->getUsers()->del($client);
        });

        $this->server->on('message', function (Server $server, Frame $frame): void {
            echo "receive from {$frame->fd}:{$frame->data}" . PHP_EOL;

            $messageRepository = $this->messageRepositories[$server->getWorkerId()];

            // frame data comes in as a string
            $output = json_decode($frame->data, true);

            $message = trim($output['message']);
            $username = trim($output['username']);

            $messageRepository->add($username, $message);

            // now we notify any of the connected clients
            foreach ($this->userRepository->getUsers() as $client) {
                $message = [
                    "text" => $message,
                    "client" => $frame->fd,
                    "username" => $username
                ];

                $server->push($client['user'], json_encode($message));
            }
        });

        $this->server->on('request', function (Request $request, Response $response) {
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
        });
    }
}
