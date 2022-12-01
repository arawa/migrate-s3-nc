<?php

namespace Db\Mapper;

use PDOException;

require_once 'lib/Entities/Storage.php';
require_once 'lib/Entities/FileUsers.php';
require_once 'lib/Entities/FileLocalStorage.php';

use Entity\FileUsers;
use Db\DatabaseSingleton;
use Entity\FileLocalStorage;

class MySqlMapper
{
    private DatabaseSingleton $database;

    public function __construct()
    {
        $this->database = DatabaseSingleton::getInstance();
    }

    /**
     * @return object where the fields are properties.
     * local value is excluded.
     * @example $listNumericId[0]->numeric_id
     */
    public function getNumericIdStorages() {
        try {

            $query = $this->database->getPdo()->query('select numeric_id from oc_storages where id not regexp "local::"');
    
            return $query->fetchAll($this->database->getPdo()::FETCH_OBJ);
            
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

            $query = $this->database->getPdo()->prepare('update oc_storages set id=:id where numeric_id=:numeric_id');
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

            $query = $this->database->getPdo()->query('select * from oc_storages where id regexp "local::"');
    
            return $query->fetch();
            
        } catch(PDOException $e) {

            die($e->getMessage());

        }
    }

    /**
     * @return Entity\Storage
     * @example $storages[0]->getNumericId()
     */
    public function getStorage($numericId) {
        try {

            $query = $this->database->getPdo()->query('select * from oc_storages where numeric_id=' . $numericId);
    
            return $query->fetchObject('Entity\\Storage');
            
        } catch(PDOException $e) {

            die($e->getMessage());

        }
    }

    /**
     * @param string $IdMimetype
     * @param string $IdStorage;
     * @return File[]
     */
    public function getFilesUsers(string $IdMimetype = null, string $IdStorage = null) {
        try {

            $args = "where oc_filecache.storage=oc_storages.numeric_id ";

            if(! is_null($IdMimetype)) {
                $args .= " and not oc_filecache.mimetype=" . $IdMimetype;
            }
            
            if(! is_null($IdStorage)) {
                $args .= " and not oc_filecache.storage=" . $IdStorage;
            }

            $args .= " order by fileid asc";

            $request = 'select fileid as file_id,
                path as relative_path,
                storage as storage_id,
                id as owner
                from oc_filecache, oc_storages ' . $args;

            $query = $this->database->getPdo()->query($request);
    
            return $query->fetchAll($this->database->getPdo()::FETCH_CLASS, FileUsers::class);
            
        } catch(PDOException $e) {

            die($e->getMessage());

        }
    }


    /**
     * @param string $IdMimetype
     * @param string $IdStorage;
     * @return
     */
    public function getFilesLocalStorage(string $IdMimetype = null, string $IdStorage = null) {
        try {

            $args = "where oc_filecache.storage=oc_storages.numeric_id ";

            if(! is_null($IdMimetype)) {
                $args .= " and not oc_filecache.mimetype=" . $IdMimetype;
            }
            
            if(! is_null($IdStorage)) {
                $args .= " and oc_filecache.storage=" . $IdStorage;
            }

            $args .= " order by fileid asc";

            $request = 'select fileid as file_id,
                path as relative_path,
                storage as storage_id,
                id as owner
                from oc_filecache, oc_storages ' . $args;

            $query = $this->database->getPdo()->query($request);
    
            return $query->fetchAll($this->database->getPdo()::FETCH_CLASS, FileLocalStorage::class);
            
        } catch(PDOException $e) {

            die($e->getMessage());

        }
    }

    /**
     * @return object where the fields are properties.
     * @example $filescaches[0]->id
     * @todo delete
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

            $query = $this->database->getPdo()->query('select * from oc_filecache ' . $args);
    
            return $query->fetchAll();
            
        } catch(PDOException $e) {

            die($e->getMessage());

        }
    }

    /** @return object[] who are the fileid like propertie.
     * @example $listFileId[0]->fileid
     * @todo delete
	 * Note: query method return a PDOStatement which implements Traversable interface.
	 * This interface is a low-level compared to Iterator interface.
	 * 
	 * @link https://www.php.net/manual/fr/class.traversable.php
	 * @link https://www.julp.fr/articles/2-4-exploitation-des-donnees-de-requetes-select.html
     */
    public function getListObjectFileid($idDirectoryUnix = null, $idLocalStorage = null) {
        try {

            $sql = 'select fileid from oc_filecache';

            $args = "";

            if(! is_null($idDirectoryUnix)) {
                $args = $args . "where not mimetype=$idDirectoryUnix";
            }
            
            if(! is_null($idLocalStorage)) {
                $args = $args . " and not storage=$idLocalStorage";
            }

            return $this->database->getPdo()->query($sql . ' ' . $args);

        } catch (PDOException $e) {

            die($e->getMessage());
            
        }
    }

    /** @return object[] who are the fileid lile propertie
     * @todo delete
     * @example $listFileIdOfFoobar[0]->fileid
     */
    public function getListObjectFileidByOwner($storage,  $idDirectoryUnix = null) {
		try {

			if (is_null($storage)) {
				print('Error, the $idOwner is not define.' . "\n");
				exit();
			}

            $sql = 'select fileid from oc_filecache where storage=' . $storage;

			$args = "";

            if(! is_null($idDirectoryUnix)) {
                $args = $args . " and not mimetype=$idDirectoryUnix";
            }

			$fullSql = $sql . $args;

            return $this->database->getPdo()->query($fullSql);
			
        } catch (PDOException $e) {

            die($e->getMessage());

        }
    }

    /**
     * @return object where the fields are properties.
     * @example $unixDirectory->id
     */
    public function getUnixDirectoryMimeType() {
        try {

            $query = $this->database->getPdo()->query('select * from oc_mimetypes where mimetype="httpd/unix-directory"');
    
            return $query->fetch();
            
        } catch(PDOException $e) {

            die($e->getMessage());

        }
    }
}