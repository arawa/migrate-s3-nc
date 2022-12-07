<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

require __DIR__ . '/vendor/autoload.php';

$finder = Finder::create()
    ->exclude([
        'images',
        'logs',
        'notes',
    ])
    ->notPath('new_config.php')
    ->in(__DIR__);

$config = new Config();
return $config->setRules([
    '@PSR12' => true,
    'array_syntax' => [
        'syntax' => 'short'
    ],
])
->setFinder($finder);