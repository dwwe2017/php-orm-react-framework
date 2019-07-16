<?php

// Load config file
require_once __DIR__ . '/../config/default-config.php';

// Use Composer autoloading
require_once __DIR__ . '/../vendor/autoload.php';

// Get Doctrine entity manager
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$proxyDir = null;
$cache = null;
$isSimpleMode = false;

$config = Setup::createAnnotationMetadataConfiguration(
    [$applicationOptions['entity_dir']],
    $applicationOptions['debug_mode'],
    $proxyDir,
    $cache,
    $isSimpleMode
);

$em = EntityManager::create($connectionOptions, $config);
