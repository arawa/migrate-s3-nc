<?php

namespace Managers;

use Db\Mapper\MySqlMapper;
use Logger\LoggerSingleton;
use Interfaces\StorageManagerInterface;

class LocalStorageManager implements StorageManagerInterface
{
    private MySqlMapper $mysqlMapper;
    
    public function __construct()
    {
        $this->mysqlMapper = new MySqlMapper();
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

        return $this->mysqlMapper->getLocalStorages();
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
        
        $this->mysqlMapper->updateIdStorage($currentId, $newId);
    }


}