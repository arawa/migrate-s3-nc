<?php

namespace MigrationS3NC\Db\Mapper;

require_once 'lib/Entities/FileUsers.php';
require_once 'lib/Entities/FileLocalStorage.php';

use PDOException;
use MigrationS3NC\Entity\FileUsers;
use MigrationS3NC\Db\DatabaseSingleton;
use MigrationS3NC\Logger\LoggerSingleton;
use MigrationS3NC\Entity\FileLocalStorage;

class FilesMapper
{

    private DatabaseSingleton $database;

    public function __construct()
    {
        $this->database = DatabaseSingleton::getInstance();
    }

    /**
     * @param string $IdMimetype
     * @param string $IdStorage;
     * @return File[]
     */
    public function getFilesUsers(string $IdMimetype = null, string $IdStorage = null) {
        try {

            $this->database->open();
            
            $args = "where oc_filecache.storage=oc_storages.numeric_id ";

            if(! is_null($IdMimetype)) {
                $args .= " and not oc_filecache.mimetype=" . $IdMimetype;
            }
            
            if(! is_null($IdStorage)) {
                $args .= " and not oc_filecache.storage=" . $IdStorage;
            }

            $args .= " order by fileid asc";

            $request = 'select fileid as file_id,
                path as relative_path,
                storage as storage_id,
                id as owner
                from oc_filecache, oc_storages ' . $args;

            $query = $this->database->getPdo()->query($request);
    
            
            $result = $query->fetchAll($this->database->getPdo()::FETCH_CLASS, FileUsers::class);

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


    /**
     * @param string $IdMimetype
     * @param string $IdStorage;
     * @return
     */
    public function getFilesLocalStorage(string $IdMimetype = null, string $IdStorage = null) {
        try {

            $this->database->open();
            
            $args = "where oc_filecache.storage=oc_storages.numeric_id ";

            if(! is_null($IdMimetype)) {
                $args .= " and not oc_filecache.mimetype=" . $IdMimetype;
            }
            
            if(! is_null($IdStorage)) {
                $args .= " and oc_filecache.storage=" . $IdStorage;
            }

            $args .= " order by fileid asc";

            $request = 'select fileid as file_id,
                path as relative_path,
                storage as storage_id,
                id as owner
                from oc_filecache, oc_storages ' . $args;

            $query = $this->database->getPdo()->query($request);


            $result = $query->fetchAll($this->database->getPdo()::FETCH_CLASS, FileLocalStorage::class);

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