<?php

require __DIR__ . '/vendor/autoload.php';

use Aws\CommandPool;
use Dotenv\Dotenv;
use Aws\S3\S3Client;
use DB\Mysql\MySqlMapper;

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
    $dotenv->required('S3_ENDPOINT')->notEmpty();
    $dotenv->required('S3_REGION')->notEmpty();
    $dotenv->required('S3_KEY')->notEmpty();
    $dotenv->required('S3_SECRET')->notEmpty();
    $dotenv->required('S3_HOSTNAME')->notEmpty();
    $dotenv->required('S3_BUCKET_NAME')->notEmpty();
}

require $_ENV['NEXTCLOUD_FOLDER_PATH'] . $_ENV['NEXTCLOUD_CONFIG_PATH'];


$NEXTCLOUD_VARIABLES_CONFIG = get_defined_vars();

$CONFIG_NEXTCLOUD = $NEXTCLOUD_VARIABLES_CONFIG['CONFIG'];

$DATADIRECTORY = $CONFIG_NEXTCLOUD['datadirectory'];

$CONCURRENCY = 1000;

$db = new MySqlMapper($_ENV['MYSQL_DATABASE_HOST'], $_ENV['MYSQL_DATABASE_SCHEMA'], $_ENV['MYSQL_DATABASE_USER'], $_ENV['MYSQL_DATABASE_PASSWORD']);

$directoryUnix = $db->getUnixDirectoryMimeType();

$localStorage = $db->getLocalStorage();

$listObjectFileid = $db->getListObjectFileid();

$s3 = new S3Client([
    'version'   => '2006-03-01',
    'region'    => $_ENV['S3_REGION'],
    'credentials'   => [
        'key'   => $_ENV['S3_KEY'],
        'secret'    =>  $_ENV['S3_SECRET'],
    ],
    'endpoint'  => $_ENV['S3_ENDPOINT'],
    'signature_version' => 'v4',
    'http' => [
        'connect_timeout' => 0,
        'delay' => 10000,
    ],
]);


/** Put user's files on a Object Storage server with concurrency.
*/
$commandGeneratorForUsers = function ($fileids) use ($s3, $db, $directoryUnix, $localStorage, $DATADIRECTORY) {
    foreach ($fileids as $fileid) {
        // fileCache : It contains the file list for each users.
        $fileCache = $db->getFileCache($fileid->fileid);
        // storage : It contains the home directory of users.
        $storage = $db->getStorage($fileCache->getStorage());

        // If it's an unix folder or for user local (ex: local::/data/nextcloud/)
        if ($fileCache->getMimeType() === $directoryUnix->id
        || $fileCache->getStorage() === $localStorage->numeric_id) {
            continue;
        }

        $dirname = dirname($DATADIRECTORY . '/' . $storage->getUid() . '/' . $fileCache->getPath());

        yield $s3->getCommand('PutObject', [
            'Bucket' => $_ENV['S3_BUCKET_NAME'],
            'Key'  => basename($dirname . '/urn:oid:' . $fileCache->getFileid()),
            'SourceFile' => $DATADIRECTORY . '/' . $storage->getUid() . '/' . $fileCache->getPath(),
        ]);

    }
};

$commandsForUsers = $commandGeneratorForUsers($listObjectFileid);

// Creating pool for users
$poolForUsers = new CommandPool($s3, $commandsForUsers, [
    'concurrency' => $CONCURRENCY,
]);

// Creating promises for users
$promiseForUsers = $poolForUsers->promise();

/** Put files of local user on a Object Storage server with promise.
*/
$listObjectFileidOfLocalUser = $db->getListObjectFileidByOwner($localStorage->numeric_id, $directoryUnix->id);
$explodePathLocalStorage =  explode('::', $localStorage->id);
$pathLocalStorage = $explodePathLocalStorage[1];

/** Put files of local user on a Object Storage server with concurrency.
*/
$commandGeneratorForLocal = function ($fileids) use ($s3, $db, $directoryUnix, $pathLocalStorage) {
    foreach($fileids as $fileidOfUserLocal) {
        // fileCache : It contains the file list for each users.
        $fileCache = $db->getFileCache($fileidOfUserLocal->fileid);
    
        if ($fileCache->getMimeType() === $directoryUnix->id) {
            continue;
        }
    
        if (file_exists($pathLocalStorage . $fileCache->getPath())) {
            // storage : It contains the home directory of users.
            $storage = $db->getStorage($fileCache->getStorage());
    
            $dirname = dirname($pathLocalStorage . $fileCache->getPath());

            yield $s3->getCommand('PutObject', [
                'Bucket' => $_ENV['S3_BUCKET_NAME'],
                'Key'   => basename($dirname . '/urn:oid:' . $fileCache->getFileid()),
                'SourceFile'    => $pathLocalStorage . $fileCache->getPath(),
            ]);
        }
    }
};


$commandsForLocal = $commandGeneratorForLocal($listObjectFileidOfLocalUser);
// Creating pool for users
$poolForLocal = new CommandPool($s3, $commandsForLocal, [
    'concurrency' => $CONCURRENCY,
]);

// Creating promises for local
$promiseForLocal = $poolForLocal->promise();

// Waitting promises
$promiseForUsers->wait();
$promiseForUsers->wait();

// Update the oc_storages database table.
// Excepted local user.
$NumericIdStorages = $db->getNumericIdStorages();
foreach($NumericIdStorages as $NumericIdStorage ) {
    $storage = $db->getStorage($NumericIdStorage->numeric_id);
    $newIdStorage = 'object::user:' . $storage->getUid();
    $db->updateIdStorage($storage->getNumericId(), $newIdStorage);
}

// Update the target datadirectory on object storage S3 server
if (in_array(strtolower($_ENV['S3_PROVIDER_NAME']), $PROVIDERS_S3_SWIFT)) {
    $newIdLocalStorage = 'object::store:' . strtolower($_ENV['S3_BUCKET_NAME']);
} else {
    $newIdLocalStorage = 'object::store:' . strtolower($_ENV['S3_PROVIDER_NAME']) . '::' . $_ENV['S3_BUCKET_NAME'];
}

$db->updateIdStorage($localStorage->numeric_id, $newIdLocalStorage);

// Creating the new config for Nextcloud
$NEW_CONFIG_NEXTCLOUD = $CONFIG_NEXTCLOUD; // Don't clone. $NEW_CONFIG_NEXTCLOUD has a new address memory.
$NEW_CONFIG_NEXTCLOUD['maintenance'] = false;
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
	    ]
    ];
}

// Creating a new_config.php file and move it by the Nextcloud's config.php file user side.
file_put_contents(__DIR__ . '/new_config.php', "<?php\n" . '$CONFIG = ' . var_export($NEW_CONFIG_NEXTCLOUD, true) . ';');

print("\nCongrulation ! The migration is done !\n");
print("You should move the new_config.php file and replace Nextcloud's config.php file with it.\n");
print("Please, check if it's new config is correct !\n\n");