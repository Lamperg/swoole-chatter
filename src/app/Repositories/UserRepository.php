<?php

namespace App\Repositories;

use Swoole\Table;
use App\Models\User;

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
        $this->users = new Table(1024 * 24);
        $this->users->column('username', (string)Table::TYPE_STRING, 64);
        $this->users->create();
    }

    public function __destruct()
    {
        if (isset($this->users)) {
            $this->users->destroy();
        }
    }

    public function getById(int $id)
    {
        /**
         * @psalm-suppress TooFewArguments
         */
        $userRow = $this->users->get((string)$id);
        if (!empty($userRow)) {
            /**
             * @psalm-suppress InvalidArrayAccess
             */
            return new User($userRow['username'], $id);
        }
        return false;
    }

    /**
     * @return User[]
     */
    public function all(): array
    {
        $collection = [];

        foreach ($this->users as $connection => $user) {
            $collection[$connection] = new User($user['username'], (int)$connection);
        }

        return $collection;
    }

    public function add(User $user)
    {
        $this->users->set((string)$user->getConnectionId(), [
            'username' => $user->getUsername()
        ]);
    }

    public function delete(int $connectionId)
    {
        $this->users->del((string)$connectionId);
    }
}
