<?php

namespace FileNextcloudConfiguration;

use Logger\LoggerSingleton;

class FileNextcloudConfiguration
{

    /**
     * @var ressource|null
     */
    private $handle;
    
    public function __construct(string $filename)
    {
        LoggerSingleton
        ::getInstance()
        ->getLogger()
        ->info('Create a template s3 configuration file for nextcloud.');
        $this->handle = fopen( __DIR__ . '/../' . $filename, "w+");
    }
    
    /**
     * @return int|bool
     */
    public function write(array $data)
    {
        LoggerSingleton
        ::getInstance()
        ->getLogger()
        ->info('Add data in the template s3 configuration file.');

        return fwrite($this->handle, "<?php\n" . '$CONFIG = ' . var_export($data, true) . ';');
    }

    public function close(): bool
    {
        LoggerSingleton
        ::getInstance()
        ->getLogger()
        ->info('The template s3 configuration file is closed.');

        return fclose($this->handle);
    }
}
