<?php

require __DIR__ . '/vendor/autoload.php';

use Aws\S3\S3Client;

use Dotenv\Dotenv;

/**
 * @param array $objects
 * @return int
 */
function checkBucketIsEmpty($objects) {
    return count($objects);
}

/**
 * @param Aws\S3\S3Client $clientS3 initialized before
 * @return \AWS\Result
 */
function getObjects($clientS3) {
    return $clientS3->listObjects(
        [
            'Bucket' => $_ENV['S3_BUCKET_NAME'],
            'MaxKeys'   => 6000,
        ]
    );
}

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dotenv->required('S3_REGION')->notEmpty();
$dotenv->required('S3_KEY')->notEmpty();
$dotenv->required('S3_SECRET')->notEmpty();
$dotenv->required('S3_ENDPOINT')->notEmpty();
$dotenv->required('S3_BUCKET_NAME')->notEmpty();

$s3 = new S3Client([
    'version'   => '2006-03-01',
    'region'    => $_ENV['S3_REGION'],
    'credentials'   => [
        'key'   => $_ENV['S3_KEY'],
        'secret'    =>  $_ENV['S3_SECRET'],
    ],
    'endpoint'  => $_ENV['S3_ENDPOINT'],
]);

$objects = getObjects($s3);

while (CheckBucketIsEmpty($objects['Contents'])) {

    foreach($objects['Contents'] as $object) {
        // To decomment if you would see the object deleted in your terminal.
        // Caution : This can slow the program.
        // print($object['Key']."\n");
        $s3->deleteObject([
            'Bucket' => $_ENV['S3_BUCKET_NAME'],
            'Key'   => $object['Key']
        ]);
    }

    $objects = getObjects($s3);
    
    if (empty($objects['Contents'])) {
        print("\nNothing objects in your bucket ! 🪣\n");
        break;
    }
}

print("\n\nIt's done ! 🎉\n\n");

print("🚨 Please, you must rollback your database, in particular the oc_storage database table, before rerun the migration program.\n\n");