<?php

require __DIR__ . '/vendor/autoload.php';

use DB\Mysql\MySqlMapper;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dotenv->required('NEXTCLOUD_FOLDER_PATH')->notEmpty();
$dotenv->required('NEXTCLOUD_CONFIG_PATH')->notEmpty();
$dotenv->required('MYSQL_DATABASE_SCHEMA')->notEmpty();
$dotenv->required('MYSQL_DATABASE_USER')->notEmpty();
$dotenv->required('MYSQL_DATABASE_PASSWORD')->notEmpty();
$dotenv->required('MYSQL_DATABASE_HOST')->notEmpty();

require $_ENV['NEXTCLOUD_FOLDER_PATH'] . $_ENV['NEXTCLOUD_CONFIG_PATH'];


$NEXTCLOUD_VARIABLES_CONFIG = get_defined_vars();

$CONFIG_NEXTCLOUD = $NEXTCLOUD_VARIABLES_CONFIG['CONFIG'];

$DATADIRECTORY = $CONFIG_NEXTCLOUD['datadirectory'];

$db = new MySqlMapper($_ENV['MYSQL_DATABASE_HOST'], $_ENV['MYSQL_DATABASE_SCHEMA'], $_ENV['MYSQL_DATABASE_USER'], $_ENV['MYSQL_DATABASE_PASSWORD']);

$storages = $db->getStorages();

$directoryUnix = $db->getUnixDirectoryMimeType();

$localStorage = $db->getLocalStorage();

foreach($storages as $storage) {
    $idExplode = explode('::', $storage->id);
    $storage->id = $idExplode[1];
}

// Create jsons folder which contents all json files.
if (!file_exists(__DIR__ . '/jsons')) {
    mkdir(__DIR__ . '/jsons');
}

/** Create json files with the name is the uid/owner of user.
 *  Fill each files an object with these keys : 
 *      - owner (uid/owner of user)
 *      - files (informations on files)
 *          - old_path (current path file)
 *          - new_path (the new file name)
 *          - new_storage (the new storage name for database)
*/
foreach($storages as $storage) {
    $filesByOwner = [];
	$filesByOwner['owner'] = $storage->id;
	$filesByOwner['files'] = [];
    $filescacheOfOwner = $db->getFilesCacheByOwner($storage->numeric_id, $directoryUnix->id, $localStorage->numeric_id);
    foreach($filescacheOfOwner as $filecache) {
        $filesByOwner['files'][] = [
            "old_path" => $DATADIRECTORY . '/' . $storage->id . '/' . $filecache->path,
            "new_path" => 'urn:oid:' . $filecache->fileid,
            "new_storage" => 'object::user:' . $storage->id
        ];
    }
    file_put_contents(__DIR__ . "/jsons/$storage->id.json", json_encode($filesByOwner, JSON_PRETTY_PRINT));
    unset($filesByOwner);
}

// Get all json files.
$jsonFiles = array_map(function ($file) {
	return __DIR__ . "/json/$file";
},
array_diff(scandir(__DIR__ . '/jsons'), ['.', '..']));

// loop all json files to process the migration and update the database.
