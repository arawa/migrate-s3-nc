<?php

namespace MigrationS3NC\Exceptions;

use Exception;

class SqlException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}