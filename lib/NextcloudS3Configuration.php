<?php

namespace MigrationS3NC;

use MigrationS3NC\Environment;
use MigrationS3NC\Logger\LoggerSingleton;
use MigrationS3NC\NextcloudConfiguration;

class NextcloudS3Configuration
{
    public static function getS3Configuration(): array
    {
        LoggerSingleton
        ::getInstance()
        ->getLogger()
        ->info('Get the new configuration s3.');

        $config = NextcloudConfiguration::getInstance()->getConfig();
        
        if (in_array(strtolower($_ENV['S3_PROVIDER_NAME']), Environment::getProvidersS3Swift())) {
            LoggerSingleton
            ::getInstance()
            ->getLogger()
            ->info('The configuration s3 is based on the Swift.');

            $config = array_merge($config, self::getS3SwitfConfig());
        } else {
            LoggerSingleton
            ::getInstance()
            ->getLogger()
            ->info('The configuration s3 is based on the S3 Compatible.');

            $config = array_merge($config, self::getS3CompatibleConfig());
        }

        return $config;
    }

    private function getS3CompatibleConfig(): array
    {
        return [
            'objectstore' =>
            [
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
            ]
        ];
    }

    private function getS3SwitfConfig(): array
    {
        return [
            'objecstore' => [
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
            ]
        ];
    }
}