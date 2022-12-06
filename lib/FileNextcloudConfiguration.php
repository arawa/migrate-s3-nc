<?php

namespace FileNextcloudConfiguration;

class FileNextcloudConfiguration
{

    /**
     * @var ressource|null
     */
    private $handle;
    
    public function __construct(string $filename)
    {
        $this->handle = fopen( __DIR__ . '/../' . $filename, "w+");
    }
    
    /**
     * @return int|bool
     */
    public function write(array $data)
    {
        return fwrite($this->handle, "<?php\n" . '$CONFIG = ' . var_export($data, true) . ';');
    }

    public function close(): bool
    {
        return fclose($this->handle);
    }
}
