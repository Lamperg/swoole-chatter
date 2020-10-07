<?php

namespace App;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Table;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class WebsocketServer
{
    protected Server $server;
    protected Table $messages;
    protected Table $connections;

    public function __construct(Router $router)
    {
        $this->createTables();

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

        $this->server->start();
    }

    private function onConnection(Request $request): void
    {
        echo "connection open: {$request->fd}\n";
        // store the client on our memory table
        $this->connections->set($request->fd, ['client' => $request->fd]);

        // update all the client with the existing messages
        foreach ($this->messages as $row) {
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
        $this->messages->set($output['username'] . time(), $output);

        // now we notify any of the connected clients
        foreach ($this->connections as $client) {
            $this->server->push($client['client'], json_encode($output));
        }
    }

    private function onClose(int $client): void
    {
        echo "client {$client} closed\n";
        // remove the client from the memory table
        $this->connections->del($client);
    }

    protected function createTables()
    {
        // Table is a shared memory table that can be used across connections
        $this->messages = new Table(1024);
        // we need to set the types that the table columns support - just like a RDB
        $this->messages->column('id', Table::TYPE_INT, 11);
        $this->messages->column('client', Table::TYPE_INT, 4);
        $this->messages->column('username', Table::TYPE_STRING, 64);
        $this->messages->column('message', Table::TYPE_STRING, 255);
        $this->messages->create();

        $this->connections = new Table(1024);
        $this->connections->column('client', Table::TYPE_INT, 4);
        $this->connections->create();
    }
}
