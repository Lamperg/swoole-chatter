<?php

namespace App\Kernel;

use App\Repositories\MessageRepository;
use App\Repositories\UserRepository;
use App\Router;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use App\Kernel\DatabaseConnectionPool;

class WebsocketServer
{
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
        $serverConfigs = [
//            "worker_num" => swoole_cpu_num() * 2
            "worker_num" => 2
        ];

        $router = new Router();
        $this->userRepository = new UserRepository();
        $this->server = new Server("app", getenv('APP_SERVER_PORT'));

        $this->server->on('workerstart', function (Server $server, int $workerId) {
            echo "worker $workerId: has been started" . PHP_EOL;

            $this->messageRepositories[$workerId] = new MessageRepository();

//            $this->messageRepository = new MessageRepository();

//            $dbPool = new DatabaseConnectionPool();
//            // get available db connection
//            $connection = $dbPool->getConnection();
//            $statement = $connection->prepare('select * from test');
//            if (!$statement) {
//                throw new RuntimeException('Prepare failed');
//            }
//            $result = $statement->execute();
//            if (!$result) {
//                throw new RuntimeException('Execute failed');
//            }
//            $result = $statement->fetchAll();
//            // move the connection back to pool
//            $dbPool->putConnection($connection);
//
//            print_r($result);
        });

        $this->server->on('workerstop', function (Server $server, int $workerId) {
            if (isset($this->messageRepositories[$workerId])) {
                unset($this->messageRepositories[$workerId]);
            }
        });

        $this->server->on('open', function (Server $server, Request $request): void {
            echo "connection open: {$request->fd}; workerId: " . $server->getWorkerId() . PHP_EOL;

            $messageRepository = $this->messageRepositories[$server->getWorkerId()];

            // store the client on our memory table
            $this->userRepository->getUsers()->set($request->fd, ['user' => $request->fd]);

//            // update all the client with the existing messages
            foreach ($messageRepository->getMessages() as $row) {
                $this->server->push($request->fd, json_encode($row));
            }
        });

        $this->server->on('message', function (Server $server, Frame $frame): void {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";

            $messageRepository = $this->messageRepositories[$server->getWorkerId()];

            // frame data comes in as a string
            $output = json_decode($frame->data, true);

            // assign a "unique" id for this message
            $output['id'] = time();
            $output['client'] = $frame->fd;

            // now we can store the message in the Table
            $messageRepository->getMessages()->set($output['username'] . time(), $output);

            // now we notify any of the connected clients
            foreach ($this->userRepository->getUsers() as $client) {
                $this->server->push($client['user'], json_encode($output));
            }
        });

        $this->server->on('close', function (Server $server, int $fd): void {
            $client = $fd;
            echo "client {$client} closed\n";
            // remove the client from the memory table
            $this->userRepository->getUsers()->del($client);
        });

        $this->server->on('request', function (Request $request, Response $response) use ($router) {
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

            $result = $router->handleRequest($method, $uri);
            // write the JSON string out
            $response->end(json_encode($result));
        });

        $this->server->set($serverConfigs);
        $this->server->start();
    }
}
