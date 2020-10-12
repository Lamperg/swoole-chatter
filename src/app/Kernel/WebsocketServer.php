<?php

namespace App\Kernel;

use App\Router;
use App\DataSource;
use RuntimeException;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class WebsocketServer
{
    const REQUEST = 'request';
    const MESSAGE = 'message';
    const CONNECTION_OPEN = 'open';
    const CONNECTION_CLOSE = 'close';
    const WORKER_START = 'workerStart';

    protected Server $server;
    protected DataSource $dataSource;

    public function __construct(Router $router, DataSource $dataSource)
    {
        $this->dataSource = $dataSource;
        $this->server = new Server("app", 9000);

        $this->server->on('open', function (Server $server, Request $request): void {
            $this->onConnection($request);
        });
        $this->server->on('message', function (Server $server, Frame $frame): void {
            $this->onMessage($frame);
        });
        $this->server->on('close', function (Server $server, int $fd): void {
            $this->onClose($fd);
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

        $this->server->on('workerStart', function (Server $server, int $workerId) {
            echo "worker $workerId: has been started" . PHP_EOL;

            $dbPool = new DatabaseConnectionPool();
            // get available db connection
            $connection = $dbPool->getConnection();
            $statement = $connection->prepare('select * from test');
            if (!$statement) {
                throw new RuntimeException('Prepare failed');
            }
            $result = $statement->execute();
            if (!$result) {
                throw new RuntimeException('Execute failed');
            }
            $result = $statement->fetchAll();
            // move the connection back to pool
            $dbPool->putConnection($connection);

            print_r($result);
        });

        $this->server->set([
//            "worker_num" => swoole_cpu_num() * 2
            "worker_num" => 2
        ]);

        $this->server->start();
    }

    private function onConnection(Request $request): void
    {
        echo "connection open: {$request->fd}\n";
        // store the client on our memory table
        $this->dataSource->getConnections()->set($request->fd, ['client' => $request->fd]);

        // update all the client with the existing messages
        foreach ($this->dataSource->getMessages() as $row) {
            $this->server->push($request->fd, json_encode($row));
        }
    }

    private function onMessage(Frame $frame): void
    {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";

        // frame data comes in as a string
        $output = json_decode($frame->data, true);

        // assign a "unique" id for this message
        $output['id'] = time();
        $output['client'] = $frame->fd;

        // now we can store the message in the Table
        $this->dataSource->getMessages()->set($output['username'] . time(), $output);

        // now we notify any of the connected clients
        foreach ($this->dataSource->getConnections() as $client) {
            $this->server->push($client['client'], json_encode($output));
        }
    }

    private function onClose(int $client): void
    {
        echo "client {$client} closed\n";
        // remove the client from the memory table
        $this->dataSource->getConnections()->del($client);
    }
}
