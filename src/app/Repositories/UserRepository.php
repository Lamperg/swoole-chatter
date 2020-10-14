<?php

namespace App\Repositories;

use Swoole\Table;

class UserRepository
{
    /**
     * Shared in memory table that can be used across connections.
     *
     * @var Table
     */
    protected Table $users;

    public function __construct()
    {
        $this->createTable();
    }

    public function getUsers(): Table
    {
        return $this->users;
    }

    protected function createTable()
    {
        if (isset($this->users)) {
            $this->users->destroy();
        }

        $this->users = new Table(1024);
        $this->users->column('user', Table::TYPE_INT, 4);
        $this->users->create();
    }
}
