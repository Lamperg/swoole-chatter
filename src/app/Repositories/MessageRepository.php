<?php

namespace App\Repositories;

use App\Kernel\DatabaseConnectionPool;
use Swoole\Table;

class MessageRepository
{
    /**
     * Shared in memory table that can be used across connections.
     *
     * @var Table
     */
    protected Table $messages;

    protected DatabaseConnectionPool $dbPool;

    public function __construct()
    {
        $this->dbPool = new DatabaseConnectionPool();

        $this->createTable();
    }

    public function getAll(): array
    {
        // get available db connection
        $connection = $this->dbPool->getConnection();

        $statement = $connection->prepare('select * from messages');
        if (!$statement) {
            throw new \RuntimeException('Prepare failed');
        }
        $result = $statement->execute();
        if (!$result) {
            throw new \RuntimeException('Execute failed');
        }

        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        // move the connection back to pool
        $this->dbPool->putConnection($connection);

        return $result;
    }

    public function add(string $client, string $username, string $message)
    {
        // get available db connection
        $connection = $this->dbPool->getConnection();

        $sql = "INSERT INTO messages (client, username, text) VALUES (?,?,?)";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$client, $username, $message]);

        // move the connection back to pool
        $this->dbPool->putConnection($connection);
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
