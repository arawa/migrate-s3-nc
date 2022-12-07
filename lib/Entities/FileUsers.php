<?php

namespace MigrationS3NC\Entity;

class FileUsers
{
    public string $file_id;
    public string $owner;
    public string $relative_path;
    public string $storage_id;

    public function __construct()
    {
        $explodePathLocalStorage = explode('::', $this->owner);
        $this->owner = $explodePathLocalStorage[1];
    }

    public function getRelativePath(): string
    {
        return $this->relative_path;
    }

    public function getFileId(): string
    {
        return $this->file_id;
    }

    public function getStorageId(): string
    {
        return $this->storage_id;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }
}
