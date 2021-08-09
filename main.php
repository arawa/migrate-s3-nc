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

function getFilesFromDir($DIR, &$files = []) {

    $DIRS = array_diff(scandir($DIR), ['.', '..']);

    $DIRS = array_map(function($file) use ($DIR) {
        return $DIR . '/' . $file;
    }, $DIRS);

    foreach($DIRS as $file) {
        if (is_dir($file)) {
            getFilesFromDir($file, $files);
        } else {
            $files[] = $file;
        }
    }
    return $files;
    
}

$NEXTCLOUD_VARIABLES_CONFIG = get_defined_vars();

$CONFIG_NEXTCLOUD = $NEXTCLOUD_VARIABLES_CONFIG['CONFIG'];

$DATADIRECTORY = $CONFIG_NEXTCLOUD['datadirectory'];

$FOLDERS = array_diff(scandir($DATADIRECTORY), ['.', '..', '.htaccess', '.ocdata', 'nextcloud.log', 'index.html']);

$usersFolders = array_filter($FOLDERS, function($FOLDER) {
    return !preg_match("/appdata_[a-zA-Z0-9]/", $FOLDER);
});

$files = [];

foreach($usersFolders as $userFolder) {
    $files[$userFolder] = getFilesFromDir($DATADIRECTORY . '/' . $userFolder);

}

$db = new MySqlMapper($_ENV['MYSQL_DATABASE_HOST'], $_ENV['MYSQL_DATABASE_SCHEMA'], $_ENV['MYSQL_DATABASE_USER'], $_ENV['MYSQL_DATABASE_PASSWORD']);

$storages = $db->getStorages();

$directoryUnix = $db->getUnixDirectoryMimeType();

$localStorage = $db->getLocalStorage();

$filescache = $db->getFilesCache($directoryUnix->id, $localStorage->numeric_id);


/** Fill the $filesFullStack foreach owner and foreach owner will be an array list 
 * of a path_file and their fileid.
 * Example : 
 * [
 *  "foo" => [ 
 *      [
 *          "path_file" => "/data/nextcloud/foo/files/Documents/Documents.odt",
 *          "fileid" => 1234
 *      ]
 *  ]
 * ]
*/
$filesFullStack = [];
foreach($files as $owner => $files) {
    $filesFullStack[$owner] = [];
    foreach($files as $file) {
        foreach($filescache as $filecache) {
            if(str_contains($DATADIRECTORY . '/' . $owner . '/' . $filecache->path, $file)) {
                $filesFullStack[$owner][] = [
                    "path_file" => $filecache->path,
                    "fileid" => $filecache->fileid
                ];
            }
        }
    }
}
