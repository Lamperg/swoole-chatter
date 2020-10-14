<?php

namespace App\Repositories;

use Swoole\Table;

class MessageRepository
{
    /**
     * Shared in memory table that can be used across connections.
     *
     * @var Table
     */
    protected Table $messages;

    public function __construct()
    {
        $this->createTable();
    }

    public function getMessages(): Table
    {
        return $this->messages;
    }

    protected function createTable()
    {
        if (isset($this->messages)) {
            $this->messages->destroy();
        }

        // Table is a shared memory table that can be used across connections
        $this->messages = new Table(1024);
        // we need to set the types that the table columns support - just like a RDB
        $this->messages->column('id', Table::TYPE_INT, 11);
        $this->messages->column('client', Table::TYPE_INT, 4);
        $this->messages->column('username', Table::TYPE_STRING, 64);
        $this->messages->column('text', Table::TYPE_STRING, 255);
        $this->messages->create();
    }
}
