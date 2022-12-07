<?php

namespace MigrationS3NC\Managers\File;

use MigrationS3NC\Configuration\NextcloudConfiguration;
use MigrationS3NC\Db\Mapper\FilesMapper;
use MigrationS3NC\Db\Mapper\MimeTypesMapper;
use MigrationS3NC\Db\Mapper\StoragesMapper;
use MigrationS3NC\Iterator\FilesLocalStorageIterator;
use MigrationS3NC\Logger\LoggerSingleton;

class FileLocalStorageManager
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
     * @return FilesLocalStorageIterator[]
     */
    public function getAll()
    {
        LoggerSingleton
        ::getInstance()
        ->getLogger()
        ->info('Get all files of the local storage.');

        $directoryUnix = $this->mimeTypesMapper->getUnixDirectoryMimeType();
        $localStorage = $this->storagesMapper->getLocalStorage();

        $dataDirectory = NextcloudConfiguration::getInstance()->getDataDirectory();

        $files = $this->filesMapper->getFilesLocalStorage($directoryUnix->id, $localStorage->numeric_id);
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