<?php

namespace S3;

use Generator;
use Aws\CommandPool;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Logger\LoggerSingleton;

class S3Manager
{
    private S3Client $s3;
    
    private const CONCURRENCY = 10;
    
    public function __construct()
    {
        $this->s3 = new S3Client([
            'version'   => '2006-03-01',
            'region'    => $_ENV['S3_REGION'],
            'credentials'   => [
                'key'   => $_ENV['S3_KEY'],
                'secret'    =>  $_ENV['S3_SECRET'],
            ],
            'endpoint'  => $_ENV['S3_ENDPOINT'],
            'signature_version' => 'v4',
        ]);
    }

    public function generatorPubObject(array $files): Generator
    {
        LoggerSingleton
        ::getInstance()
        ->getLogger()
        ->info('Generator commands PutObject for each file.');

        foreach ($files as $file) {
            yield $this->s3->getCommand('PutObject', [
                'Bucket' => $_ENV['S3_BUCKET_NAME'],
                'Key'  => basename($file->getDirname() . '/urn:oid:' . $file->getFileid()),
                'SourceFile' => $file->getAbsolutePath(),
            ]);
        }
    }

    public function pool(Generator $commands): CommandPool
    {
        LoggerSingleton
        ::getInstance()
        ->getLogger()
        ->info('Starting the migration to S3.');

        return new CommandPool($this->s3, $commands, [
            'concurrency' => self::CONCURRENCY,
            'rejected' => function (AwsException $reason, $iterKey) {
                LoggerSingleton
                ::getInstance()
                ->getLogger()
                ->error('The programm stopped during the migration.', [
                    'reason' => $reason,
                    'iter_key' => $iterKey
                ]);
            }
        ]);
    }
}
