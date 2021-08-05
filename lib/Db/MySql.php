<?php


namespace DB\Mysql;

use PDO;
use PDOException;

class MySql extends PDO {

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
            $this::ATTR_ERRMODE => $this::ERRMODE_EXCEPTION
        ]);
    }
    
    /**
     * @return array where the fields are properties.
     * local value is excluded.
     * @example $storage->id
     */
    public function getStorages() {
        try {

            $query = $this->query('select * from oc_storages where id not regexp "local"');
    
            return $query->fetchAll($this::FETCH_OBJ);
            
        } catch(PDOException $e) {

            die($e->getMessage());

        }
    }
}