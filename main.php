<?php

require __DIR__ . '/vendor/autoload.php';

use Constants\Constants;
use Environment\Environment;
use Managers\FileLocalStorageManager;
use Managers\FileUserManager;
use Managers\StorageManager;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use NextcloudConfiguration\NextcloudConfiguration;
use S3\S3Manager;

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

// Waitting promises
$migrateLogger->info('Waitting promises');
$promise->wait();

// update the oc_storages table database
$storageManager = new StorageManager();
foreach($storageManager->getAll() as $storage ) {
    $storageManager->updateId($storage->getNumericId(), Constants::ID_USER_OBJECT . $storage->getUid());
}

$localStorage = $storageManager->getLocalStorage();

if (in_array(strtolower($_ENV['S3_PROVIDER_NAME']), Environment::getProvidersS3Swift())) {
    $idObjectStorage = Constants::ID_S3_COMPATIBLE_OBJECT . strtolower($_ENV['S3_BUCKET_NAME']);
} else {
    $idObjectStorage = Constants::ID_S3_AMAZON_OBJECT . $_ENV['S3_BUCKET_NAME'];
}

$storageManager->updateId($localStorage->numeric_id, $idObjectStorage);

// Creating the new config for Nextcloud
$migrateLogger->info('Preparing the new config file for Nextcloud');
$NEW_CONFIG_NEXTCLOUD = NextcloudConfiguration::getInstance()->getConfig(); // Don't clone. $NEW_CONFIG_NEXTCLOUD has a new address memory.
if (in_array(strtolower($_ENV['S3_PROVIDER_NAME']), Environment::getProvidersS3Swift())) {
    $NEW_CONFIG_NEXTCLOUD['objectstore'] = [
        'class' => 'OC\\Files\\ObjectStore\\Swift',
        'arguments' => [
            'autocreate'    => true,
            'user' => [
                'name' => $_ENV['S3_SWIFT_USERNAME'],
                'password' => $_ENV['S3_SWIFT_PASSWORD'],
                'domain' => [
                    'name'  => 'default'
                ],
            ],
            'scope' => [
                'project' => [
                    'name' => $_ENV['S3_SWIFT_ID_PROJECT'],
                    'domain' => [
                        'name' => 'default',
                    ],
                ],
            ],
            'serviceName' => 'swift',
            'region'    => strtoupper($_ENV['S3_REGION']),
            'url'   =>  $_ENV['S3_SWIFT_URL'],
            'bucket' => $_ENV['S3_BUCKET_NAME'],
        ]
    ];
} else {
    $NEW_CONFIG_NEXTCLOUD['objectstore'] = [
        'class' => '\\OC\\Files\\ObjectStore\\S3',
        'arguments' => [
            'bucket' => $_ENV['S3_BUCKET_NAME'],
            'autocreate'    => true,
            'key'   => $_ENV['S3_KEY'],
            'secret'    => $_ENV['S3_SECRET'],
            'hostname'  => $_ENV['S3_HOSTNAME'],
            'port'  => intval($_ENV['S3_PORT']),
            'use_ssl'   => true,
            'region'    => strtolower($_ENV['S3_REGION']),
            'use_path_style' => true
	    ]
    ];
}

// Creating a new_config.php file and move it by the Nextcloud's config.php file user side.
file_put_contents(__DIR__ . '/new_config.php', "<?php\n" . '$CONFIG = ' . var_export($NEW_CONFIG_NEXTCLOUD, true) . ';');

print("\nCongrulation ! The migration is done ! 🎉 🪣\n");
print("You should move the new_config.php file and replace Nextcloud's config.php file with it.\n");
print("Please, check if it's new config is correct !\n\n");
$migrateLogger->info('It\'s over');