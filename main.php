<?php

require __DIR__ . '/vendor/autoload.php';

use S3\S3Manager;
use Constants\Constants;
use Service\StorageService;
use Environment\Environment;
use Managers\FileUserManager;
use Managers\HomeStorageManager;
use Managers\LocalStorageManager;
use Managers\FileLocalStorageManager;
use NextcloudS3Configuration\NextcloudS3Configuration;
use FileNextcloudConfiguration\FileNextcloudConfiguration;

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
