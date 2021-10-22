<?php

namespace Entity;

class Filecache
{
    private $fileid;
    private $storage;
    private $path;
    private $path_hash;
    private $parent;
    private $name;
    private $mimetype;
    private $mimepart;
    private $size;
    private $mtime;
    private $storage_mtime;
    private $encrypted;
    private $unencrypted_size;
    private $etag;
    private $permissions;
    private $checksum;

    public function __construct()
    {
        // if ($this->mimetype)   
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