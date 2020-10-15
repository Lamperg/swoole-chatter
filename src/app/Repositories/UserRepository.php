<?php

namespace App\Repositories;

use App\Models\User;
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
        if (isset($this->users)) {
            $this->users->destroy();
        }

        $this->users = new Table(1024 * 24);
        $this->users->column('username', Table::TYPE_STRING, 64);
        $this->users->create();
    }

    /**
     * @return User[]
     */
    public function getAll(): array
    {
        $result = [];

        foreach ($this->users as $connection => $user) {
            $result[] = new User($user['username'], $connection);
        }

        return $result;
    }

    public function add(User $user)
    {
        $this->users->set($user->getConnectionId(), [
            'username' => $user->getUsername()
        ]);
    }

    public function remove(int $connectionId)
    {
        $this->users->del($connectionId);
    }
}
