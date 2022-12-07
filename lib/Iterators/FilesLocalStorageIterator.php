<?php

namespace MigrationS3NC\Iterator;

class FilesLocalStorageIterator implements \Iterator
{
    
    private int $position = 0;

    private array $files = [];
    
    public function __construct(array $files)
    {
        $this->files = $files;
    }
    
    public function rewind(): void {
        $this->poisition = 0;
    }

    public function current() {
        return $this->files[$this->position];
    }

    public function key(): int {
        return $this->poistion;
    }

    public function next(): void {
        ++$this->position;
    }

    public function valid(): bool {
        return isset($this->files[$this->position]);
    }

    public function getRelativePath(): string {
        return $this->files['relative_path'];
    }

    public function getAbsolutePath(): string {
        return $this->files['absolute_path'];
    }

    public function getFileId(): string {
        return $this->files['file_id'];
    }

    public function getStorageId(): string {
        return $this->files['storage_id'];
    }

    public function getOwner(): string {
        return $this->files['owner'];
    }

    public function getDataDirectory(): string {
        return $this->files['data_directory'];
    }

    public function getDirname(): string {
        return $this->files['dirname'];
    }
    
}
