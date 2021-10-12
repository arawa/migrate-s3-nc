<?php

require __DIR__ . '/vendor/autoload.php';

use Aws\S3\S3Client;
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
 *      - new_storage (the new storage name for database)
 *      - files (informations on files)
 *          - old_path (current path file)
 *          - new_path (the new file name)
*/
foreach($storages as $storage) {
    $filesByOwner = [];
	$filesByOwner['owner'] = $storage->id;
	$filesByOwner['new_storage'] = 'object::user:' . $storage->id;
	$filesByOwner['files'] = [];
    $filescacheOfOwner = $db->getFilesCacheByOwner($storage->numeric_id, $directoryUnix->id, $localStorage->numeric_id);
    foreach($filescacheOfOwner as $filecache) {
		/** @todo putObject here ? */
		$dirname = dirname($DATADIRECTORY . '/' . $storage->id . '/' . $filecache->path);
        $filesByOwner['files'][] = [
            "old_path" => $DATADIRECTORY . '/' . $storage->id . '/' . $filecache->path,
            "new_path" => $dirname . '/urn:oid:' . $filecache->fileid,
            "old_basename" => basename($DATADIRECTORY . '/' . $storage->id . '/' . $filecache->path),
            "new_basename" => basename($dirname . '/urn:oid:' . $filecache->fileid)
        ];
    }
	/** @todo update table here ? **/
    file_put_contents(__DIR__ . "/jsons/$storage->id.json", json_encode($filesByOwner, JSON_PRETTY_PRINT));
    unset($filesByOwner);
	unset($filescacheOfOwner);
}

// Get all json files.
$jsonFiles = array_filter(scandir(__DIR__ . '/jsons'), function($file) {
    if ($file !== '.' && $file !== '..') {
        return $file;
    }
});

$jsonFiles = array_map(function ($file) {
    return __DIR__ . "/jsons/" . $file;
}, $jsonFiles);

// loop all json files to process the migration and update the database.

// {
//     'path_file' =>
//     string(77) "/data/nextcloud/nc-s3.dev.arawa.fr/bfotia/files/Documents/Chanson mariage.odt"
//     'fileid' =>
//     string(4) "4455"
//   }
// {
/**
 * @todo To search how rename the file at the same time as push
 */
$s3 = new S3Client([
    'version'   => '2006-03-01',
    'region'    => $_ENV['S3_REGION'],
    'credentials'   => [
        'key'   => $_ENV['S3_KEY'],
        'secret'    =>  $_ENV['S3_SECRET'],
    ],
    'endpoint'  => $_ENV['S3_ENDPOINT'],
]);

foreach ($jsonFiles as $jsonFile) {
    $json = json_decode(file_get_contents($jsonFile), true);
    foreach( $json['files'] as $file) {
        $s3->putObject([
            'Bucket' => $_ENV['S3_BUCKET_NAME'],
            'Key'   => $file['new_basename'],
            'SourceFile'    => $file['base_path'],
        ]);
    }
}
