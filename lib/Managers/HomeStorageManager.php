<?php

namespace Managers;

use Db\Mapper\MySqlMapper;
use Entity\Storage;
use Interfaces\StorageManagerInterface;

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