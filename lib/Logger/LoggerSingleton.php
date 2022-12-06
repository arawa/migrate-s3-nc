<?php

namespace Logger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LogLevel;

include __DIR__ . "/../functions.php";

class LoggerSingleton
{
    private const PATH_LOGGER_FOLDER = __DIR__ . '/../../'; 

    /**
     * @var LoggerSingleton|null
     */
    private static $instance = null;

    private static Logger $logger;

    private function __construct()
    {
        $dateFormat = "Y-m-d H:i";
        $output = "[%level_name%] %datetime% %message% %context%\n";
        $formatter = new LineFormatter($output, $dateFormat);

        if ( !is_dir(self::PATH_LOGGER_FOLDER .'logs')) {
            mkdir(self::PATH_LOGGER_FOLDER .'logs');
        }

        $stream = new StreamHandler(
            self::PATH_LOGGER_FOLDER . 'logs/migrateS3-'. formatDate() .'.log',
            LogLevel::DEBUG
        );
        $stream->setFormatter($formatter);

        self::$logger = new Logger('migrate-s3');
        self::$logger->pushHandler($stream);
    }

    /**
     * @return LoggerSingleton|null
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new LoggerSingleton();
        }

        return self::$instance;
    }

    public static function getLogger(): Logger
    {
        return self::$logger;
    }
}
