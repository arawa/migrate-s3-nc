<?php

namespace MigrationS3NC\Managers;

use MigrationS3NC\Db\Mapper\MySqlMapper;
use MigrationS3NC\Iterator\FilesLocalStorageIterator;
use MigrationS3NC\Logger\LoggerSingleton;
use MigrationS3NC\NextcloudConfiguration;

class FileLocalStorageManager
{
    private MySqlMapper $mysqlMapper;

    public function __construct()
    {
        $this->mysqlMapper = new MySqlMapper();
    }

    /**
     * @return FilesLocalStorageIterator[]
     */
    public function getAll()
    {
        LoggerSingleton
        ::getInstance()
        ->getLogger()
        ->info('Get all files of the local storage.');

        $directoryUnix = $this->mysqlMapper->getUnixDirectoryMimeType();
        $localStorage = $this->mysqlMapper->getLocalStorage();

        $dataDirectory = NextcloudConfiguration::getInstance()->getDataDirectory();

        $files = $this->mysqlMapper->getFilesLocalStorage($directoryUnix->id, $localStorage->numeric_id);
        $newFiles = [];
        foreach ($files as $file) {
            $newFiles[] = new FilesLocalStorageIterator([
                'relative_path' => $file->getRelativePath(),
                'absolute_path' => $dataDirectory . '/' . $file->getRelativePath(),
                'dirname' => dirname($dataDirectory . '/' . $file->getRelativePath()),
                'owner' => $file->getOwner(),
                'file_id' => $file->getFileId(),
                'storage_id' => $file->getStorageId(),
            ]);
        }

        return $newFiles;
    }
}