<?php

namespace Entity;

class Filecache
{
    private $fileid;
    private $storage;
    private $path;
    private $mimetype;

    public function __construct()
    {

    }

    public function getPath() {
        return $this->path;
    }

    public function getMimeType() {
        return $this->mimetype;
    }

    public function getStorage() {
        return $this->storage;
    }

    public function getFileid() {
        return $this->fileid;
    }

}