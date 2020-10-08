<?php

namespace App;

use Swoole\Table;

class DataSource
{
    protected Table $messages;
    protected Table $connections;

    public function __construct()
    {
        $this->createTables();
    }

    public function getMessages(): Table
    {
        return $this->messages;
    }

    public function getConnections(): Table
    {
        return $this->connections;
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
