<?php

namespace MigrationS3NC\Managers\Storage;

use MigrationS3NC\Db\Mapper\StoragesMapper;
use MigrationS3NC\Entity\Storage;
use MigrationS3NC\Interfaces\StorageManagerInterface;
use MigrationS3NC\Logger\LoggerSingleton;

class HomeStorageManager implements StorageManagerInterface
{
    private StoragesMapper $storagesMapper;

    public function __construct()
    {
        $this->storagesMapper = new StoragesMapper();
    }

    /**
     * @return Storage[]
     */
    public function getAll()
    {
        LoggerSingleton::getInstance()
        ->getLogger()
        ->info('Get all Home storages.');

        return $this->storagesMapper->getHomeStorages();
    }

    public function updateId($currentId, $newId): void
    {
        LoggerSingleton::getInstance()
        ->getLogger()
        ->info('Update the id storage.', [
            'current_id' => $currentId,
            'new_id' => $newId
        ]);

        $this->storagesMapper->updateIdStorage($currentId, $newId);
    }

    public function getLocalStorage()
    {
        return $this->storagesMapper->getLocalStorage();
    }
}
