<?php

namespace MigrationS3NC\Db\Mapper;

use MigrationS3NC\Db\DatabaseSingleton;
use MigrationS3NC\Logger\LoggerSingleton;
use PDOException;

class MimeTypesMapper
{
    private DatabaseSingleton $database;

    public function __construct()
    {
        $this->database = DatabaseSingleton::getInstance();
    }

    /**
     * @return object where the fields are properties.
     * @example $unixDirectory->id
     */
    public function getUnixDirectoryMimeType() {
        try {

            $this->database->open();

            $query = $this->database->getPdo()->query('select * from oc_mimetypes where mimetype="httpd/unix-directory"');
    
            $result = $query->fetch();
            
            $this->database->close();

            return $result;
            
        } catch(PDOException $e) {

            LoggerSingleton
            ::getInstance()
            ->getLogger()
            ->error($e->getMessage());

            die($e->getMessage());

        }
    }
}
