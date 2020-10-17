<?php

namespace App;

use Swoole\WebSocket\Server;
use App\Handlers\MessageHandler;
use App\Handlers\WorkerStopHandler;
use App\Handlers\WorkerStartHandler;
use App\Repositories\UserRepository;
use App\Repositories\MessageRepository;
use App\Handlers\ConnectionOpenHandler;
use App\Handlers\ConnectionCloseHandler;

class Application
{
    protected Server $server;
    protected UserRepository $userRepository;
    protected MessageRepository $messageRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->messageRepository = new MessageRepository();
        $this->server = new Server("app", getenv('APP_SERVER_PORT'));

        $this->setHandlers();
    }

    /**
     * Runs the application.
     */
    public function run()
    {
        $configs = [
            "worker_num" => swoole_cpu_num() * 2
        ];

        $this->server->set($configs);
        $this->server->start();
    }

    /**
     * Registers server's events.
     */
    protected function setHandlers()
    {
        $this->server->on('message', new MessageHandler(
            $this->userRepository,
            $this->messageRepository
        ));

        $this->server->on('open', new ConnectionOpenHandler(
            $this->userRepository,
            $this->messageRepository
        ));

        $this->server->on('close', new ConnectionCloseHandler(
            $this->userRepository
        ));

        $this->server->on('workerStart', new WorkerStartHandler());

        $this->server->on('workerStop', new WorkerStopHandler());
    }
}
