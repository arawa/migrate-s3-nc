<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Monolog\Logger;
use Aws\CommandPool;
use Aws\S3\S3Client;
use Db\DatabaseSingleton;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Managers\FileLocalStorageManager;
use Managers\FileUserManager;
use Managers\StorageManager;

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

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dotenv->required('NEXTCLOUD_FOLDER_PATH')->notEmpty();
$dotenv->required('NEXTCLOUD_CONFIG_PATH')->notEmpty();
$dotenv->required('MYSQL_DATABASE_SCHEMA')->notEmpty();
$dotenv->required('MYSQL_DATABASE_USER')->notEmpty();
$dotenv->required('MYSQL_DATABASE_PASSWORD')->notEmpty();
$dotenv->required('MYSQL_DATABASE_HOST')->notEmpty();
$dotenv->required('S3_PROVIDER_NAME')->notEmpty();

$PROVIDERS_S3_SWIFT = ['openstack', 'swift', 'ovh'];

// Required the good env vars.
if (in_array(strtolower($_ENV['S3_PROVIDER_NAME']), $PROVIDERS_S3_SWIFT)) {
    $migrateLogger->info('The S3 server is Swift');
    $dotenv->required('S3_ENDPOINT')->notEmpty();
    $dotenv->required('S3_SWIFT_URL')->notEmpty();
    $dotenv->required('S3_REGION')->notEmpty();
    $dotenv->required('S3_SWIFT_USERNAME')->notEmpty();
    $dotenv->required('S3_KEY')->notEmpty();
    $dotenv->required('S3_SECRET')->notEmpty();
    $dotenv->required('S3_SWIFT_PASSWORD')->notEmpty();
    $dotenv->required('S3_BUCKET_NAME')->notEmpty();
    $dotenv->required('S3_SWIFT_ID_PROJECT')->notEmpty();

} else {
    $migrateLogger->info('The S3 server is S3 Compatbile');
    $dotenv->required('S3_ENDPOINT')->notEmpty();
    $dotenv->required('S3_REGION')->notEmpty();
    $dotenv->required('S3_KEY')->notEmpty();
    $dotenv->required('S3_SECRET')->notEmpty();
    $dotenv->required('S3_HOSTNAME')->notEmpty();
    $dotenv->required('S3_BUCKET_NAME')->notEmpty();
}

require $_ENV['NEXTCLOUD_FOLDER_PATH'] . $_ENV['NEXTCLOUD_CONFIG_PATH'];


$migrateLogger->info('Get Nextcloud\'s config');
$NEXTCLOUD_VARIABLES_CONFIG = get_defined_vars();

$CONFIG_NEXTCLOUD = $NEXTCLOUD_VARIABLES_CONFIG['CONFIG'];

$DATADIRECTORY = $CONFIG_NEXTCLOUD['datadirectory'];

$CONCURRENCY = 10;

$s3 = new S3Client([
    'version'   => '2006-03-01',
    'region'    => $_ENV['S3_REGION'],
    'credentials'   => [
        'key'   => $_ENV['S3_KEY'],
        'secret'    =>  $_ENV['S3_SECRET'],
    ],
    'endpoint'  => $_ENV['S3_ENDPOINT'],
    'signature_version' => 'v4',
]);

$fileManager = new FileUserManager();

/** Put user's files on a Object Storage server with concurrency.
*/
$migrateLogger->info('Preparing to send users\' files asynchronously');
$commandGeneratorForUsers = function (array $filesUser) use ($s3) {
    foreach ($filesUser as $fileUser) {
        yield $s3->getCommand('PutObject', [
            'Bucket' => $_ENV['S3_BUCKET_NAME'],
            'Key'  => basename($fileUser->getDirname() . '/urn:oid:' . $fileUser->getFileid()),
            'SourceFile' => $fileUser->getAbsolutePath(),
        ]);

    }
};

$fileUsers = $fileManager->getAll();
DatabaseSingleton::getInstance()->close();

$commandsForUsers = $commandGeneratorForUsers($fileUsers);

// Creating pool for users
$poolForUsers = new CommandPool($s3, $commandsForUsers, [
    'concurrency' => $CONCURRENCY,
]);

// Creating promises for users
$migrateLogger->info('Start uploading files to the S3 server for users');
$promiseForUsers = $poolForUsers->promise();

/** Put files of local user on a Object Storage server with promise.
*/
DatabaseSingleton::getInstance()->reopen();
$filesLocalStorageManager = new FileLocalStorageManager();

/** Put files of local user on a Object Storage server with concurrency.
*/
$migrateLogger->info('Preparing to send LocalUser\'s files asynchronously');
$commandGeneratorForLocal = function ($filesLocalUserIterator) use ($s3) {
    foreach($filesLocalUserIterator as $fileLocalUser) {
        if (!file_exists($fileLocalUser->getAbsolutePath())) {
            continue;
        }

        yield $s3->getCommand('PutObject', [
            'Bucket' => $_ENV['S3_BUCKET_NAME'],
            'Key'   => basename($fileLocalUser->getDirname() . '/urn:oid:' . $fileLocalUser->getFileid()),
            'SourceFile'    => $fileLocalUser->getAbsolutePath(),
        ]);
    }
};

$filesLocalStorage = $filesLocalStorageManager->getAll();
DatabaseSingleton::getInstance()->close();
$commandsForLocal = $commandGeneratorForLocal($filesLocalStorage);
// Creating pool for users
$poolForLocal = new CommandPool($s3, $commandsForLocal, [
    'concurrency' => $CONCURRENCY,
]);

// Creating promises for local
$migrateLogger->info('Start uploading files to the S3 server for the UserLocal');
$promiseForLocal = $poolForLocal->promise();

// Waitting promises
$migrateLogger->info('Waitting promises');
$promiseForUsers->wait();
$promiseForLocal->wait();

DatabaseSingleton::getInstance()->reopen();
$storageManager = new StorageManager();

// Update the oc_storages database table.
// Excepted local user.
$migrateLogger->info('Updating the Storage database table foreach users');
$numericIdStorages = $storageManager->getAllNumericId();
foreach($numericIdStorages as $numericIdStorage ) {
    $storage = $storageManager->get($numericIdStorage->numeric_id);
    $newIdStorage = 'object::user:' . $storage->getUid();
    $storageManager->updateId($storage->getNumericId(), $newIdStorage);
}

// Update the target datadirectory on object storage S3 server
if (in_array(strtolower($_ENV['S3_PROVIDER_NAME']), $PROVIDERS_S3_SWIFT)) {
    $migrateLogger->info('Updating the target datadirectory for S3 Swift');
    $newIdLocalStorage = 'object::store:' . strtolower($_ENV['S3_BUCKET_NAME']);
} else {
    $migrateLogger->info('Updating the target datadirectory for S3 Compatible');
    $newIdLocalStorage = 'object::store:amazon::' . $_ENV['S3_BUCKET_NAME'];
}
$migrateLogger->info('Updating the Storage database table for LocalUser');
$localStorage = $storageManager->getLocalStorage();
$storageManager->updateId($localStorage->numeric_id, $newIdLocalStorage);

// Creating the new config for Nextcloud
$migrateLogger->info('Preparing the new config file for Nextcloud');
$NEW_CONFIG_NEXTCLOUD = $CONFIG_NEXTCLOUD; // Don't clone. $NEW_CONFIG_NEXTCLOUD has a new address memory.
if (in_array(strtolower($_ENV['S3_PROVIDER_NAME']), $PROVIDERS_S3_SWIFT)) {
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
DatabaseSingleton::getInstance()->close();
$migrateLogger->info('It\'s over');