<?php

namespace App\Repositories;

use App\Models\Message;
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

    public function all(): array
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

    /**
     * @param Message $message
     * @return Message
     */
    public function add(Message $message): Message
    {
        // get available db connection
        $connection = $this->dbPool->getConnection();

        $sql = "INSERT INTO messages (username, text, date) VALUES (:username, :text, :date)";
        $stmt = $connection->prepare($sql);
        $stmt->execute([
            "text" => $message->getText(),
            "username" => $message->getUsername(),
            "date" => $message->getDate()->format('Y-m-d H:i:s'),
        ]);

        $message->setId($connection->lastInsertId());

        // move the connection back to pool
        $this->dbPool->putConnection($connection);

        return $message;
    }
}
