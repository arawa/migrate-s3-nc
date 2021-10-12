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

$s3 = new S3Client([
    'version'   => '2006-03-01',
    'region'    => $_ENV['S3_REGION'],
    'credentials'   => [
        'key'   => $_ENV['S3_KEY'],
        'secret'    =>  $_ENV['S3_SECRET'],
    ],
    'endpoint'  => $_ENV['S3_ENDPOINT'],
]);

/** Put for each files on a Object Storage server and
 * update the database table.
*/
foreach($storages as $storage) {
    $filescacheOfOwner = $db->getFilesCacheByOwner($storage->numeric_id, $directoryUnix->id, $localStorage->numeric_id);
	$newStorage = 'object::user:' . $storage->id;
    foreach($filescacheOfOwner as $filecache) {
		/** @todo putObject here ? */
		$dirname = dirname($DATADIRECTORY . '/' . $storage->id . '/' . $filecache->path);
		$s3->putObject([
            'Bucket' => $_ENV['S3_BUCKET_NAME'],
            'Key'   => basename($dirname . '/urn:oid:' . $filecache->fileid),
            'SourceFile'    => $DATADIRECTORY . '/' . $storage->id . '/' . $filecache->path,
        ]);
    }
	/** @todo update table here ? **/
}
