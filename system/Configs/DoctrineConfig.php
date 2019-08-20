<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Configs;


use Configula\ConfigFactory;
use Configula\ConfigValues;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Exceptions\DoctrineException;
use Helpers\DeclarationHelper;
use Helpers\FileHelper;
use Interfaces\ConfigInterfaces\VendorExtensionConfigInterface;
use Traits\ConfigTraits\VendorExtensionInitConfigTrait;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;
use Webmasters\Doctrine\ORM\EntityManager;

/**
 * Class DoctrineConfig
 * @package Configs Revised and added options of the configuration file
 * @see ModuleManager::$cacheConfig
 */
class DoctrineConfig implements VendorExtensionConfigInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitConfigTrait;

    /**
     * DoctrineConfig constructor.
     * @param ConfigValues $config
     * @throws DoctrineException
     */
    public function __construct(ConfigValues $config)
    {
        $this->config = $config;

        $baseDir = $this->config->get("base_dir");
        $defaultConfigPath = sprintf("%s/config/default-config.php", $this->config->get("base_dir"));
        $optionsDefault = $this->getOptionsDefault();

        /**
         * Build connection options
         */
        $connectionOptionsDefault = ["connection_options" => $optionsDefault["connection_options"]];
        $connectionOptions = ["connection_options" => $this->config->get("connection_options")];
        $connectionOptions = ConfigFactory::fromArray($connectionOptionsDefault)->mergeValues($connectionOptions);

        /**
         * Check connection configuration
         */
        $connectionOption = $connectionOptions->get("connection_options.connection_option");
        $connection = $connectionOptions->get(sprintf("connection_options.%s", $connectionOption), false);

        if (!$connectionOptions) {
            throw new DoctrineException(sprintf("The global configuration file '%s' did not specify a valid database connection", $defaultConfigPath), E_ERROR);
        } elseif (!$connection || count($connection) < 6) {
            throw new DoctrineException(sprintf("The '%s' field of the global configuration file '%s' does not contain a valid database connection", $connectionOption, $defaultConfigPath), E_ERROR);
        }

        /**
         * Build doctrine application options
         */
        $doctrineOptionsDefault = ["doctrine_options" => $optionsDefault["doctrine_options"]];
        $doctrineOptions = ["doctrine_options" => $this->config->get("doctrine_options")];
        $doctrineOptions["doctrine_options"]["base_dir"] = $baseDir;
        $doctrineOptions["doctrine_options"]["em_class"] = EntityManager::class;
        $doctrineOptions["doctrine_options"]["gedmo_ext"] = ["Timestampable"];
        $doctrineOptions["doctrine_options"]["proxy_dir"] = sprintf("%s/data/proxy/%s", $baseDir, $connectionOption);
        $doctrineOptions["doctrine_options"]["vendor_dir"] = sprintf("%s/vendor", $baseDir);
        $doctrineOptions = ConfigFactory::fromArray($doctrineOptionsDefault)->mergeValues($doctrineOptions);

        /**
         * Create and check individual paths
         */
        $entityDir = sprintf("%s/%s", $baseDir, $doctrineOptions->get("doctrine_options.entity_dir"));
        FileHelper::init($entityDir, DoctrineException::class)->isReadable();

        /**
         * Merge file values with absolute path
         */
        $doctrineOptions = $doctrineOptions->mergeValues(!$doctrineOptions->has("doctrine_options.entity_namespace") ? [
            "doctrine_options" => [
                "entity_dir" => $entityDir,
                "entity_namespace" => basename($entityDir)
            ]
        ] : [
            "doctrine_options" => [
                "entity_dir" => $entityDir,
                "entity_namespace" => $doctrineOptions->get("doctrine_options.entity_namespace")
            ]
        ]);

        $defaultProxyDir = $doctrineOptions->get("doctrine_options.proxy_dir");
        FileHelper::init($defaultProxyDir, DoctrineException::class)->isWritable(true);

        $this->configValues = ConfigValues::fromConfigValues($connectionOptions)->merge($doctrineOptions);
    }

    /**
     * @return array
     */
    public function getOptionsDefault(): array
    {
        $isDebug = $this->config->get("debug_mode");
        $baseDir = $this->config->get("base_dir");
        $cacheDriver = new ArrayCache();

        if (!$isDebug) {
            if (DeclarationHelper::init("apcu", null, "apcu_add")->isDeclared()) {
                $cacheDriver = new ApcuCache();
            } else {
                $filesystemCacheDir = sprintf("%s/data/cache/doctrine", $baseDir);
                if (FileHelper::init($filesystemCacheDir)->isWritable(true)) {
                    $cacheDriver = new FilesystemCache($filesystemCacheDir);
                }
            }
        }

        return [
            "connection_options" => [
                "connection_option" => "default",
                "default" => [
                    "driver" => "pdo_mysql",
                    "dbname" => "",
                    "host" => "",
                    "user" => "",
                    "password" => "",
                    "prefix" => "",
                ]
            ],
            "doctrine_options" => [
                "autogenerate_proxy_classes" => true,
                "debug_mode" => $isDebug,
                "entity_dir" => "system/Entities",
                "cache" => $cacheDriver
            ]
        ];
    }
}