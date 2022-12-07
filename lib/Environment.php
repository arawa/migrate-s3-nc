<?php

namespace MigrationS3NC;

use Dotenv\Dotenv;
use MigrationS3NC\Logger\LoggerSingleton;

class Environment
{
    private const PROVIDERS_S3_SWIFT = [
        'openstack',
        'swift',
        'ovh'
    ];

    /**
     * Return a list of providers s3 swift.
     */
    public static function getProvidersS3Swift(): array
    {
        return self::PROVIDERS_S3_SWIFT;
    }

    /**
     * Load the environment variables.
     */
    public static function load(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();

        LoggerSingleton::getInstance()
            ->getLogger()
            ->info('Starting loading environment variables.');

        self::loadMysqlEnv($dotenv);
        self::loadNextcloudEnv($dotenv);
        self::loadS3CommonEnv($dotenv);

        if (in_array(
            strtolower($_ENV['S3_PROVIDER_NAME']),
            self::PROVIDERS_S3_SWIFT
        )) {
            self::loadS3SwiftEnv($dotenv);
        }
    }

    private function loadMysqlEnv(Dotenv $dotenv): void
    {
        LoggerSingleton::getInstance()
        ->getLogger()
        ->info('Loading the environment variables for the Mysql database.');

        $dotenv->required('MYSQL_DATABASE_SCHEMA')->notEmpty();
        $dotenv->required('MYSQL_DATABASE_USER')->notEmpty();
        $dotenv->required('MYSQL_DATABASE_PASSWORD')->notEmpty();
        $dotenv->required('MYSQL_DATABASE_HOST')->notEmpty();
    }

    private function loadS3CommonEnv(Dotenv $dotenv): void
    {
        LoggerSingleton::getInstance()
        ->getLogger()
        ->info('Loading the environment variables for the S3 common.');

        $dotenv->required('S3_ENDPOINT')->notEmpty();
        $dotenv->required('S3_REGION')->notEmpty();
        $dotenv->required('S3_KEY')->notEmpty();
        $dotenv->required('S3_SECRET')->notEmpty();
        $dotenv->required('S3_BUCKET_NAME')->notEmpty();
        $dotenv->required('S3_PROVIDER_NAME')->notEmpty();
        $dotenv->required('S3_HOSTNAME')->notEmpty();
    }

    private function loadS3SwiftEnv(Dotenv $dotenv): void
    {
        LoggerSingleton::getInstance()
        ->getLogger()
        ->info('Loading the environment variables for the S3 Swift.');

        $dotenv->required('S3_SWIFT_URL')->notEmpty();
        $dotenv->required('S3_SWIFT_USERNAME')->notEmpty();
        $dotenv->required('S3_SWIFT_PASSWORD')->notEmpty();
        $dotenv->required('S3_SWIFT_ID_PROJECT')->notEmpty();
    }

    private function loadNextcloudEnv(Dotenv $dotenv): void
    {
        LoggerSingleton::getInstance()
        ->getLogger()
        ->info('Loading the environment variables for the Nextcloud.');

        $dotenv->required('NEXTCLOUD_FOLDER_PATH')->notEmpty();
        $dotenv->required('NEXTCLOUD_CONFIG_PATH')->notEmpty();
    }
}
