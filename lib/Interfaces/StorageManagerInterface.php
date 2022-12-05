<?php

namespace Interfaces;

use Entity\Storage;

interface StorageManagerInterface
{
    /**
     * @return Storage[]
     */
    public function getAll();
    
    public function updateId(string $currentId, string $newId): void;
}
