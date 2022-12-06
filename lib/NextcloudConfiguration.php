<?php

namespace NextcloudConfiguration;

use Logger\LoggerSingleton;

class NextcloudConfiguration
{
    /**
     * @var NextcloudConfiguration|null
     */
    private static $instance = null;

    private array $CONFIG_NEXTCLOUD;
    
    private function __construct()
    {
        LoggerSingleton
        ::getInstance()
        ->getLogger()
        ->info('Get current the nextcloud configuration.');

        require $_ENV['NEXTCLOUD_FOLDER_PATH'] . $_ENV['NEXTCLOUD_CONFIG_PATH'];
        $NEXTCLOUD_VARIABLES_CONFIG = get_defined_vars();
        $this->CONFIG_NEXTCLOUD = $NEXTCLOUD_VARIABLES_CONFIG['CONFIG'];        
    }

    public static function getInstance(): NextcloudConfiguration
    {
        if (is_null(self::$instance))
        {
            self::$instance = new NextcloudConfiguration();
        }

        return self::$instance;
    }

    public function getDataDirectory(): string
    {
        return $this->CONFIG_NEXTCLOUD['datadirectory'];
    }

    public function getConfig(): array
    {
        return $this->CONFIG_NEXTCLOUD;
    }
}
