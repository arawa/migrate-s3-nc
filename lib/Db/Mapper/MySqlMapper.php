<?php

namespace Db\Mapper;

use PDOException;

require_once 'lib/Entities/Storage.php';
require_once 'lib/Entities/FileUsers.php';
require_once 'lib/Entities/FileLocalStorage.php';

use Entity\Storage;
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

    public function getStoragesOfUsers()
    {
        try {
            $this->database->open();

            $query = $this->database->getPdo()->query('select * from oc_storages where id not regexp "local::"');
    
            $result = $query->fetchAll($this->database->getPdo()::FETCH_CLASS, Storage::class);
            
            $this->database->close();

            return $result;

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     * update the id storage (not numeric_id)
     */
    public function updateIdStorage($numericId, $newId) {
        // update
        try {

            $this->database->open();

            $query = $this->database->getPdo()->prepare('update oc_storages set id=:id where numeric_id=:numeric_id');
            $query->execute([
                'id'    => $newId,
                'numeric_id'    => $numericId
            ]);

            $this->database->close();

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

            $this->database->open();
            
            $query = $this->database->getPdo()->query('select * from oc_storages where id regexp "local::"');
    
            $result = $query->fetch();
            
            $this->database->close();

            return $result;
            
        } catch(PDOException $e) {

            die($e->getMessage());

        }
    }

    /**
     * @return Entity\Storage
     * @example $storages[0]->getNumericId()
     * @todo delete
     */
    public function getStorage($numericId) {
        try {

            $this->database->open();
            
            $query = $this->database->getPdo()->query('select * from oc_storages where numeric_id=' . $numericId);
    
            $result = $query->fetchObject('Entity\\Storage');

            $this->database->close();
            
            return $result;
            
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

            $this->database->open();
            
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
    
            
            $result = $query->fetchAll($this->database->getPdo()::FETCH_CLASS, FileUsers::class);

            $this->database->close();

            return $result;
            
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

            $this->database->open();
            
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


            $result = $query->fetchAll($this->database->getPdo()::FETCH_CLASS, FileLocalStorage::class);

            $this->database->close();

            return $result;
            
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

            $this->database->open();

            $args = "where storage=$idOwner";

            if(! is_null($idDirectoryUnix)) {
                $args = $args . " and not mimetype=$idDirectoryUnix";
            }
            
            if(! is_null($idLocalStorage)) {
                $args = $args . " and not storage=$idLocalStorage";
            }

            $query = $this->database->getPdo()->query('select * from oc_filecache ' . $args);
            $result = $query->fetchAll();
    
            $this->database->close();
            
            return $result;
            
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

            $this->database->open();

            $sql = 'select fileid from oc_filecache';

            $args = "";

            if(! is_null($idDirectoryUnix)) {
                $args = $args . "where not mimetype=$idDirectoryUnix";
            }
            
            if(! is_null($idLocalStorage)) {
                $args = $args . " and not storage=$idLocalStorage";
            }

            $result = $this->database->getPdo()->query($sql . ' ' . $args);

            $this->database->close();

            return $result;

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

            $this->database->open();

            $sql = 'select fileid from oc_filecache where storage=' . $storage;

			$args = "";

            if(! is_null($idDirectoryUnix)) {
                $args = $args . " and not mimetype=$idDirectoryUnix";
            }

			$fullSql = $sql . $args;

            $this->database->close();

            $result = $this->database->getPdo()->query($fullSql);
            
            return $result;
			
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

            $this->database->open();

            $query = $this->database->getPdo()->query('select * from oc_mimetypes where mimetype="httpd/unix-directory"');
    
            $result = $query->fetch();
            
            $this->database->close();

            return $result;
            
        } catch(PDOException $e) {

            die($e->getMessage());

        }
    }
}