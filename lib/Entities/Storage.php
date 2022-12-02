<?php

namespace Entity;

class Storage
{
    private $numeric_id;

    private $id;

    private $available;

    private $last_checked;

    private $uid;

    public function __construct()
    {
        if (isset($this->id)) {
            $idExplode = explode('::', $this->id);
            $this->uid = $idExplode[1];
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getNumericId() {
        return $this->numeric_id;
    }

    public function getAvailable() {
        return $this->available;
    }

    public function getLasetChecked() {
        return $this->last_checked;
    }

    public function getUid() {
        return $this->uid;
    }
}
