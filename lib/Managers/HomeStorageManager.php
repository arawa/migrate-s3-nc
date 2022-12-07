<?php

namespace MigrationS3NC\Managers;

use MigrationS3NC\Db\Mapper\MySqlMapper;
use MigrationS3NC\Entity\Storage;
use MigrationS3NC\Interfaces\StorageManagerInterface;
use MigrationS3NC\Logger\LoggerSingleton;

class HomeStorageManager implements StorageManagerInterface
{
    private MysqlMapper $mysqlMapper;

    public function __construct()
    {
        $this->mysqlMapper = new MySqlMapper();
    }

    /**
     * @return Storage[]
     */
    public function getAll()
    {
        LoggerSingleton
        ::getInstance()
        ->getLogger()
        ->info('Get all Home storages.');

        return $this->mysqlMapper->getHomeStorages();
    }

    /**
     * @todo delete
     */
    public function get($numericId)
    {
        return $this->mysqlMapper->getStorage($numericId);
    }

    public function updateId($currentId, $newId): void
    {
        LoggerSingleton
        ::getInstance()
        ->getLogger()
        ->info('Update the id storage.', [
            'current_id' => $currentId,
            'new_id' => $newId
        ]);

        $this->mysqlMapper->updateIdStorage($currentId, $newId);
    }

    /**
     * @todo delete
     */
    public function getLocalStorage()
    {
        return $this->mysqlMapper->getLocalStorage();
    }


}