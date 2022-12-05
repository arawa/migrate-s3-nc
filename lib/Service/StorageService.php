<?php

namespace Service;

use Constants\Constants;
use Environment\Environment;

class StorageService
{
    public static function getNewIdLocalStorage(): string
    {
        if (in_array(strtolower($_ENV['S3_PROVIDER_NAME']), Environment::getProvidersS3Swift())) {
            return Constants::ID_S3_COMPATIBLE_OBJECT . strtolower($_ENV['S3_BUCKET_NAME']);
        }

        return Constants::ID_S3_AMAZON_OBJECT . $_ENV['S3_BUCKET_NAME'];
    }
}