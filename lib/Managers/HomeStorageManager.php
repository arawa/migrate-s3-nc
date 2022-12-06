<?php

namespace Managers;

use Entity\Storage;
use Db\Mapper\MySqlMapper;
use Logger\LoggerSingleton;
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