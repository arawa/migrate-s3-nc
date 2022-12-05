<?php

namespace Managers;

use Db\Mapper\MySqlMapper;
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
        return $this->mysqlMapper->getLocalStorages();
    }

    public function updateId(string $currentId, string $newId): void
    {
        $this->mysqlMapper->updateIdStorage($currentId, $newId);
    }


}