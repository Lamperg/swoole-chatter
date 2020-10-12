<?php

namespace App\Kernel;

use Swoole\Database\PDOPool;
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOProxy;

class DatabaseConnectionPool
{
    protected PDOPool $pool;

    public function __construct()
    {
        $connectionConfigs = (new PDOConfig())
            ->withHost(getenv('DB_HOST'))
            ->withPort(getenv('DB_PORT'))
            ->withDbName(getenv('DB_NAME'))
            ->withUsername(getenv('DB_USER'))
            ->withPassword(getenv('DB_PASSWORD'));

        $this->pool = new PDOPool($connectionConfigs);
    }

    /**
     * Gets an available db connection from the pool.
     *
     * @return \PDO|PDOProxy
     */
    public function getConnection()
    {
        return $this->pool->get();
    }

    /**
     * Puts used db connection back to the pool.
     *
     * @param PDOProxy $connection
     */
    public function putConnection(PDOProxy $connection): void
    {
        $this->pool->put($connection);
    }
}
