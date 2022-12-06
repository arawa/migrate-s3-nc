<?php

namespace MigrationS3NC\Db;

use MigrationS3NC\Db\Pdo\MySqlPDO;
use PDO;

class DatabaseSingleton
{
    /**
     * @var DatabaseSingleton|null
     */
    private static $instance = null;

    /**
     * @var PDO|null
     */
    private $pdo = null;

    private function __construct()
    {
        $this->pdo = new MySqlPDO();
    }

    public static function getInstance(): DatabaseSingleton {
        if (is_null(self::$instance))
        {
            self::$instance = new DatabaseSingleton();
        }

        return self::$instance;
    }

    public function close(): void
    {
        $this->pdo = null;
    }

    public function open(): void
    {
        if (is_null($this->pdo))
        {
            $this->pdo = new MySqlPDO();
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}