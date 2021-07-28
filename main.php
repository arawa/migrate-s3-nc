<?php

use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dotenv->required('NEXTCLOUD_FOLDER_PATH')->notEmpty();
$dotenv->required('NEXTCLOUD_CONFIG_PATH')->notEmpty();

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

$usersFolders = array_diff(scandir($DATADIRECTORY), ['.', '..', '.htaccess', '.ocdata', 'nextcloud.log', 'index.html']);

$files = [];

foreach($usersFolders as $userFolder) {
    $files[$userFolder] = getFilesFromDir($DATADIRECTORY . '/' . $userFolder);

}
