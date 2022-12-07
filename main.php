<?php

require __DIR__ . '/vendor/autoload.php';

use MigrationS3NC\Configuration\NextcloudS3Configuration;
use MigrationS3NC\Constants;
use MigrationS3NC\Environment;
use MigrationS3NC\File\FileNextcloudConfiguration;
use MigrationS3NC\Managers\File\FileLocalStorageManager;
use MigrationS3NC\Managers\File\FileUserManager;
use MigrationS3NC\Managers\S3\S3Manager;
use MigrationS3NC\Managers\Storage\HomeStorageManager;
use MigrationS3NC\Managers\Storage\LocalStorageManager;
use MigrationS3NC\Service\StorageService;

Environment::load();

$fileManager = new FileUserManager();
$filesLocalStorageManager = new FileLocalStorageManager();

$s3Manager = new S3Manager();
$commands = $s3Manager->generatorPubObject(
    array_merge(
        $fileManager->getAll(),
        $filesLocalStorageManager->getAll()
    )
);

$pool = $s3Manager->pool($commands);
$promise = $pool->promise();
$promise->wait();

// update the oc_storages table database
$HomeStorageManager = new HomeStorageManager();
foreach($HomeStorageManager->getAll() as $storage ) {
    $HomeStorageManager
        ->updateId(
            $storage->getNumericId(),
            Constants::ID_USER_OBJECT . $storage->getUid()
        );
}

$localStorageManager = new LocalStorageManager();
// We manage a monobucket for the momment...
$idObjectStorage = StorageService::getNewIdLocalStorage();
$localStorage = $localStorageManager->getAll()[0];
$localStorageManager->updateId($storage->getNumericId(), $idObjectStorage);

$data = NextcloudS3Configuration::getS3Configuration();
$file = new FileNextcloudConfiguration("new_config.php");
$file->write($data);
$file->close();

print("\nCongrulation ! The migration is done ! 🎉 🪣\n");
print("You should move the new_config.php file and replace Nextcloud's config.php file with it.\n");
print("Please, check if it's new config is correct !\n\n");
