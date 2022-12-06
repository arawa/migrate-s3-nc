<?php

require __DIR__ . '/vendor/autoload.php';

use S3\S3Manager;
use Monolog\Logger;
use Constants\Constants;
use Service\StorageService;
use Environment\Environment;
use Managers\FileUserManager;
use Managers\HomeStorageManager;
use Managers\LocalStorageManager;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Managers\FileLocalStorageManager;
use NextcloudS3Configuration\NextcloudS3Configuration;
use FileNextcloudConfiguration\FileNextcloudConfiguration;

include "lib/functions.php";

// Custom logger
$dateFormatForLog = "Y-m-d\TH:i:sP";
$outputForLog = "[%datetime%] %level_name% %message% %context% %extra%\n";
$formatterForLog = new LineFormatter($outputForLog, $dateFormatForLog);

if ( !is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs');
}

$streamForLog = new StreamHandler(__DIR__ . '/logs/migrateS3-'. formatDate() .'.log', Logger::DEBUG);
$streamForLog->setFormatter($formatterForLog);
$migrateLogger = new Logger('mugrateS3');
$migrateLogger->pushHandler($streamForLog);

$migrateLogger->info('Get all development environments');

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
    $HomeStorageManager->updateId($storage->getNumericId(), Constants::ID_USER_OBJECT . $storage->getUid());
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
