<?php

namespace App\Repositories;

use PDO;
use App\Models\Message;
use App\Utilities\DatabaseConnectionPool;

class MessageRepository
{
    protected DatabaseConnectionPool $dbPool;

    public function __construct()
    {
        $this->dbPool = new DatabaseConnectionPool();
    }

    /**
     * Retrieves collection of all existed messages.
     *
     * @return Message[]
     */
    public function all(): array
    {
        // get available db connection
        $connection = $this->dbPool->getConnection();

        $stmt = $connection->query('select * from messages');
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // move the connection back to pool
        $this->dbPool->putConnection($connection);

        $collection = [];
        foreach ($result as $item) {
            $message = new Message($item["username"], $item["text"]);
            $message->setDate(\DateTime::createFromFormat('Y-m-d H:i:s', $item['date']));
            $message->setId($item["id"]);

            $collection[] = $message;
        }

        return $collection;
    }

    /**
     * Saves provided message and returns saved info.
     *
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
