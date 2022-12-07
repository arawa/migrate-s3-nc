<?php

namespace MigrationS3NC\Managers\Storage;

use MigrationS3NC\Db\Mapper\StoragesMapper;
use MigrationS3NC\Interfaces\StorageManagerInterface;
use MigrationS3NC\Logger\LoggerSingleton;

class LocalStorageManager implements StorageManagerInterface
{
    private StoragesMapper $storagesMapper;
    
    public function __construct()
    {
        $this->storagesMapper = new StoragesMapper();
    }

    /**
     * @return Entity\Storage[]
     */
    public function getAll()
    {
        LoggerSingleton
        ::getInstance()
        ->getLogger()
        ->info('Get all Local storages.');

        return $this->storagesMapper->getLocalStorages();
    }

    public function updateId(string $currentId, string $newId): void
    {
        LoggerSingleton
        ::getInstance()
        ->getLogger()
        ->info('Update the id storage', [
            'current_id' => $currentId,
            'new_id' => $newId
        ]);
        
        $this->storagesMapper->updateIdStorage($currentId, $newId);
    }


}