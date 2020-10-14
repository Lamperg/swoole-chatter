<?php

namespace App\Repositories;

use PDO;
use RuntimeException;
use App\Kernel\DatabaseConnectionPool;

class MessageRepository
{
    protected DatabaseConnectionPool $dbPool;

    public function __construct()
    {
        $this->dbPool = new DatabaseConnectionPool();
    }

    public function getAll(): array
    {
        // get available db connection
        $connection = $this->dbPool->getConnection();

        $statement = $connection->prepare('select * from messages');
        if (!$statement) {
            throw new RuntimeException('Prepare failed');
        }
        $result = $statement->execute();
        if (!$result) {
            throw new RuntimeException('Execute failed');
        }

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        // move the connection back to pool
        $this->dbPool->putConnection($connection);

        return $result;
    }

    public function add(string $username, string $message)
    {
        // get available db connection
        $connection = $this->dbPool->getConnection();

        $sql = "INSERT INTO messages (username, text) VALUES (:username, :text)";
        $stmt = $connection->prepare($sql);
        $stmt->execute([
            "text" => $message,
            "username" => $username,
        ]);

        // move the connection back to pool
        $this->dbPool->putConnection($connection);
    }
}
