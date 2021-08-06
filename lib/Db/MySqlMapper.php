<?php


namespace DB\Mysql;

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
    
            return $query->fetchAll();
            
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
     * @return array where the fields are properties.
     * @example $filescache[0]->id
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
    
            return $query->fetchAll();
            
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