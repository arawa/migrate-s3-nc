<?php

namespace MigrationS3NC\Db\Pdo;

use PDO;

class MySqlPDO extends PDO
{
    public function __construct()
    {
        parent::__construct(
            "mysql:dbname=$_ENV[MYSQL_DATABASE_SCHEMA];host=$_ENV[MYSQL_DATABASE_HOST]",
            $_ENV['MYSQL_DATABASE_USER'],
            $_ENV['MYSQL_DATABASE_PASSWORD'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                PDO::ATTR_PERSISTENT => true
            ]
        );
    }
}
