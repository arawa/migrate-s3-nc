<?php

namespace MigrationS3NC\Service;

use MigrationS3NC\Constants;
use MigrationS3NC\Environment;
use MigrationS3NC\Logger\LoggerSingleton;

class StorageService
{
    public static function getNewIdLocalStorage(): string
    {
        if (in_array(strtolower($_ENV['S3_PROVIDER_NAME']), Environment::getProvidersS3Swift())) {
            LoggerSingleton::getInstance()
            ->getLogger()
            ->info('Get the new id local storage.', [
                Constants::ID_S3_COMPATIBLE_OBJECT . strtolower($_ENV['S3_BUCKET_NAME'])
            ]);

            return Constants::ID_S3_COMPATIBLE_OBJECT . strtolower($_ENV['S3_BUCKET_NAME']);
        }

        LoggerSingleton::getInstance()
        ->getLogger()
        ->info('Get the new id local storage.', [
            Constants::ID_S3_AMAZON_OBJECT . $_ENV['S3_BUCKET_NAME']
        ]);

        return Constants::ID_S3_AMAZON_OBJECT . $_ENV['S3_BUCKET_NAME'];
    }
}
