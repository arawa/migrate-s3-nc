<?php

namespace Managers;

use Db\Mapper\MySqlMapper;

class StorageManager
{
    private MysqlMapper $mysqlMapper;

    public function __construct()
    {
        $this->mysqlMapper = new MySqlMapper();
    }

    public function getAllNumericId()
    {
        return $this->mysqlMapper->getNumericIdStorages();
    }

    public function get($numericId)
    {
        return $this->mysqlMapper->getStorage($numericId);
    }

    public function updateId($currentId, $newId)
    {
        return $this->mysqlMapper->updateIdStorage($currentId, $newId);
    }

    public function getLocalStorage()
    {
        return $this->mysqlMapper->getLocalStorage();
    }


}