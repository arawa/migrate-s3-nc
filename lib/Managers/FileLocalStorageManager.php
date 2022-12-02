<?php

namespace Managers;

use Db\Mapper\MySqlMapper;
use Iterator\FilesLocalStorageIterator;

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
        $directoryUnix = $this->mysqlMapper->getUnixDirectoryMimeType();
        $localStorage = $this->mysqlMapper->getLocalStorage();

        require $_ENV['NEXTCLOUD_FOLDER_PATH'] . $_ENV['NEXTCLOUD_CONFIG_PATH'];

        $NEXTCLOUD_VARIABLES_CONFIG = get_defined_vars();
        
        $CONFIG_NEXTCLOUD = $NEXTCLOUD_VARIABLES_CONFIG['CONFIG'];
        
        $dataDirectory = $CONFIG_NEXTCLOUD['datadirectory'];

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