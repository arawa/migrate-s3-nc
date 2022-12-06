<?php

namespace Managers;

use Db\Mapper\MySqlMapper;
use Logger\LoggerSingleton;
use Iterator\FilesUserIterator;
use NextcloudConfiguration\NextcloudConfiguration;

class FileUserManager
{
    private MysqlMapper $mysqlMapper;

    public function __construct()
    {
        $this->mysqlMapper = new MySqlMapper();
    }

    /**
     * @return FilesUserIterator[]
     */
    public function getAll()
    {
        LoggerSingleton
        ::getInstance()
        ->getLogger()
        ->info('Get all files of the users without taking into account the local storage.');

        $directoryUnix = $this->mysqlMapper->getUnixDirectoryMimeType();
        $localStorage = $this->mysqlMapper->getLocalStorage();
        
        $dataDirectory = NextcloudConfiguration::getInstance()->getDataDirectory();

        $files = $this->mysqlMapper->getFilesUsers($directoryUnix->id, $localStorage->numeric_id);
        $newFiles = [];
        foreach ($files as $file) {
            $newFiles[] = new FilesUserIterator([
                'relative_path' => $file->getRelativePath(),
                'absolute_path' => $dataDirectory . '/' . $file->getOwner() . '/' . $file->getRelativePath(),
                'dirname' => dirname($dataDirectory . '/' . $file->getOwner() . '/' . $file->getRelativePath()),
                'owner' => $file->getOwner(),
                'file_id' => $file->getFileId(),
                'storage_id' => $file->getStorageId(),
            ]);
        }

        return $newFiles;
    }
}