<?php

namespace MigrationS3NC\Db\Mapper;

require_once 'lib/Entities/Storage.php';

use PDOException;
use MigrationS3NC\Entity\Storage;
use MigrationS3NC\Db\DatabaseSingleton;
use MigrationS3NC\Exceptions\SqlException;
use MigrationS3NC\Logger\LoggerSingleton;

class StoragesMapper
{
    private DatabaseSingleton $database;

    public function __construct()
    {
        $this->database = DatabaseSingleton::getInstance();
    }

    /**
     * @return Storage[]
     */
    public function getHomeStorages()
    {
        try {
            $this->database->open();

            $query = $this->database->getPdo()->query('select * from oc_storages where id not regexp "local::"');

            $result = $query->fetchAll($this->database->getPdo()::FETCH_CLASS, Storage::class);

            $this->database->close();

            return $result;
        } catch (PDOException $e) {
            LoggerSingleton::getInstance()
            ->getLogger()
            ->error($e->getMessage());

            throw new SqlException($e->getMessage());
        }
    }

    /**
     * @return Storage[]
     */
    public function getLocalStorages()
    {
        try {
            $this->database->open();

            $query = $this->database->getPdo()->query('select * from oc_storages where id like "%local::%"');

            $result = $query->fetchAll($this->database->getPdo()::FETCH_CLASS, Storage::class);

            $this->database->close();

            return $result;
        } catch (PDOException $e) {
            LoggerSingleton::getInstance()
            ->getLogger()
            ->error($e->getMessage());

            throw new SqlException($e->getMessage());
        }
    }

    /**
     * update the id storage (not numeric_id)
     */
    public function updateIdStorage($numericId, $newId)
    {
        try {
            $this->database->open();

            $query = $this->database->getPdo()->prepare('update oc_storages set id=:id where numeric_id=:numeric_id');
            $query->execute([
                'id'    => $newId,
                'numeric_id'    => $numericId
            ]);

            $this->database->close();
        } catch(PDOException $e) {
            LoggerSingleton::getInstance()
            ->getLogger()
            ->error($e->getMessage());

            throw new SqlException($e->getMessage());
        }
    }

    /**
     * @return object where the fields are properties.
     * @example $localStorage->id
     */
    public function getLocalStorage()
    {
        try {
            $this->database->open();

            $query = $this->database->getPdo()->query('select * from oc_storages where id regexp "local::"');

            $result = $query->fetch();

            $this->database->close();

            return $result;
        } catch(PDOException $e) {
            LoggerSingleton::getInstance()
            ->getLogger()
            ->error($e->getMessage());

            throw new SqlException($e->getMessage());
        }
    }
}
