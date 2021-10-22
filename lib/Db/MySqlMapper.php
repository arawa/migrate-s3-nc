<?php

namespace DB\Mysql;

require_once 'lib/Entities/Storage.php';
require_once 'lib/Entities/Filecache.php';

use PDO;
use PDOException;

class MySqlMapper extends PDO {

    /** @var string */
    private $HOST;

    /** @var string */
    private $DB_NAME;

    /** @var string */
    private $USER;

    /** @var string */
    private $dsn;

    public function __construct($HOST, $DB_NAME, $USER, $PASSWORD)
    {
        $this->HOST = $HOST;
        $this->DB_NAME = $DB_NAME;
        $this->USER = $USER;
        $this->PASSWORD = $PASSWORD;
        $this->dsn = "mysql:dbname=$this->DB_NAME;host=$this->HOST";
        // ERRMODE_EXCEPTION allows be define exceptions as errors.
        parent::__construct($this->dsn, $this->USER, $this->PASSWORD,  [
            $this::ATTR_ERRMODE => $this::ERRMODE_EXCEPTION,
            $this::ATTR_DEFAULT_FETCH_MODE => $this::FETCH_OBJ
        ]);
    }
    
    /**
     * @return array where the fields are properties.
     * local value is excluded.
     * @example $storages[0]->id
     */
    public function getStorages() {
        try {

            $query = $this->query('select * from oc_storages where id not regexp "local"');
    
            return $query->fetchAll($this::FETCH_CLASS, 'Entity\\Storage');
            
        } catch(PDOException $e) {

            die($e->getMessage());

        }
    }

        /**
     * @return object where the fields are properties.
     * local value is excluded.
     * @example $listNumericId[0]->numeric_id
     */
    public function getNumericIdStorages() {
        try {

            $query = $this->query('select numeric_id from oc_storages where id not regexp "local"');
    
            return $query->fetchAll($this::FETCH_OBJ);
            
        } catch(PDOException $e) {

            die($e->getMessage());

        }
    }


    /**
     * update the id storage (not numeric_id)
     */
    public function updateIdStorage($numericId, $newId) {
        // update
        try {

            $query = $this->prepare('update oc_storages set id=:id where numeric_id=:numeric_id');
            $query->execute([
                'id'    => $newId,
                'numeric_id'    => $numericId
            ]);

        } catch(PDOException $e) {

            die($e->getMessage());
            
        }
    }
    
    /**
     * @return object where the fields are properties.
     * @example $localStorage->id
     */
    public function getLocalStorage() {
        try {

            $query = $this->query('select * from oc_storages where id regexp "local"');
    
            return $query->fetch();
            
        } catch(PDOException $e) {

            die($e->getMessage());

        }
    }

    /**
     * @return Entity\Storage[]
     * @example $storages[0]->getNumericId()
     */
    public function getStorage($numericId) {
        try {

            $query = $this->query('select * from oc_storages where numeric_id=' . $numericId);
    
            return $query->fetchObject('Entity\\Storage');
            
        } catch(PDOException $e) {

            die($e->getMessage());

        }
    }

    /**
     * @return Entity\Filecache[]
     * @example $filescache[0]->getId()
     */
    public function getFilesCache($idDirectoryUnix = null, $idLocalStorage = null) {
        try {

            $args = "";

            if(! is_null($idDirectoryUnix)) {
                $args = $args . "where not mimetype=$idDirectoryUnix";
            }
            
            if(! is_null($idLocalStorage)) {
                $args = $args . " and not storage=$idLocalStorage";
            }

            $query = $this->query('select * from oc_filecache ' . $args);
    
            return $query->fetchAll($this::FETCH_CLASS, 'Entity\\Filecache');
            
        } catch(PDOException $e) {

            die($e->getMessage());

        }
    }

    /**
     * @return Entity\Filecache
     * @example $filecache->getId()
     */
    public function getFileCache($id) {
        try {

            $query = $this->query('select * from oc_filecache where fileid='. $id);
    
            return $query->fetchObject('Entity\\Filecache');
            
        } catch(PDOException $e) {

            die($e->getMessage());

        }
    }

    /**
     * @return object where the fields are properties.
     * @example $filescaches[0]->id
     */
    public function getFilesCacheByOwner($idOwner, $idDirectoryUnix = null, $idLocalStorage = null) {
        try {

			if (is_null($idOwner)) {
				print('Error, the $idOwner is not define.' . "\n");
				exit();
			}

            $args = "where storage=$idOwner";

            if(! is_null($idDirectoryUnix)) {
                $args = $args . " and not mimetype=$idDirectoryUnix";
            }
            
            if(! is_null($idLocalStorage)) {
                $args = $args . " and not storage=$idLocalStorage";
            }

            $query = $this->query('select * from oc_filecache ' . $args);
    
            return $query->fetchAll();
            
        } catch(PDOException $e) {

            die($e->getMessage());

        }
    }

    /** @return object[] who are the fileid like propertie.
     * @example $listFileId[0]->fileid
     */
    public function getListObjectFileid() {
        try {
            $query = $this->query('select fileid from oc_filecache');
            return $query->fetchAll($this::FETCH_OBJ);
        } catch(PDOException $e) {
            die($e->getMessage());
        }
    }

    /** @return object[] who are the fileid lile propertie
     * @example $listFileIdOfFoobar[0]->fileid
     */
    public function getListObjectFileidByOwner($storage) {
        try {
            $query = $this->query('select fileid from oc_filecache where storage=' . $storage);
            return $query->fetchAll($this::FETCH_OBJ);
        } catch(PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     * @return object where the fields are properties.
     * @example $unixDirectory->id
     */
    public function getUnixDirectoryMimeType() {
        try {

            $query = $this->query('select * from oc_mimetypes where mimetype="httpd/unix-directory"');
    
            return $query->fetch();
            
        } catch(PDOException $e) {

            die($e->getMessage());

        }
    }
}