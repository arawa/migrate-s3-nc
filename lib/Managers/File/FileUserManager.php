<?php

namespace MigrationS3NC\Managers\File;

use MigrationS3NC\Configuration\NextcloudConfiguration;
use MigrationS3NC\Db\Mapper\FilesMapper;
use MigrationS3NC\Db\Mapper\MimeTypesMapper;
use MigrationS3NC\Db\Mapper\StoragesMapper;
use MigrationS3NC\Iterator\FilesUserIterator;
use MigrationS3NC\Logger\LoggerSingleton;

class FileUserManager
{
    private FilesMapper $filesMapper;
    private MimeTypesMapper $mimeTypesMapper;
    private StoragesMapper $storagesMapper;

    public function __construct()
    {
        $this->filesMapper = new FilesMapper();
        $this->mimeTypesMapper = new MimeTypesMapper();
        $this->storagesMapper = new StoragesMapper();
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

        $directoryUnix = $this->mimeTypesMapper->getUnixDirectoryMimeType();
        $localStorage = $this->storagesMapper->getLocalStorage();
        
        $dataDirectory = NextcloudConfiguration::getInstance()->getDataDirectory();

        $files = $this->filesMapper->getFilesUsers($directoryUnix->id, $localStorage->numeric_id);
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