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
use Services\DoctrineService;
use Traits\ConfigTraits\VendorExtensionInitConfigTrait;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;
use Webmasters\Doctrine\ORM\EntityManager;

/**
 * Class DoctrineConfig
 * @package Configs Revised and added options of the configuration file
 * @see ModuleManager::$doctrineConfig
 */
class DoctrineConfig implements VendorExtensionConfigInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitConfigTrait;

    /**
     * DoctrineConfig constructor.
     * @param DefaultConfig $defaultConfig
     * @throws DoctrineException
     * @see ModuleManager::__construct()
     */
    public final function __construct(DefaultConfig $defaultConfig)
    {
        $this->config = $defaultConfig->getConfigValues();

        $baseDir = $this->config->get("base_dir");
        $moduleBaseDir = $defaultConfig->getModuleBaseDir();
        $moduleShortName = $defaultConfig->getModuleShortName();
        $defaultConfigPath = sprintf("%s/config/default-config.php", $this->config->get("base_dir"));
        $defaultOptions = $this->getOptionsDefault();

        /**
         * Build connection options
         */
        $connectionOptionsDefault = ["connection_options" => $defaultOptions["connection_options"]];
        $connectionOptions = ["connection_options" => $this->config->get("connection_options")];
        $connectionOptions = ConfigFactory::fromArray($connectionOptionsDefault)->mergeValues($connectionOptions);

        /**
         * Check default connection and configuration
         */
        $connectionOption = $connectionOptions->get("connection_options.connection_option");
        $connection = $connectionOptions->get(sprintf("connection_options.%s", $connectionOption), false);
        $connectionDriver = $connectionOptions->get(sprintf("connection_options.%s.driver", $connectionOption));

        /**
         * Check db connection
         */
        if (strcasecmp($connectionDriver, "pdo_sqlite") != 0) {
            if (!$connection || count($connection) < 6) {
                throw new DoctrineException(sprintf("The '%s' field of the global configuration file '%s' does not contain a valid database connection", $connectionOption, $defaultConfigPath), E_ERROR);
            }
        }

        /**
         * Build application option for system
         */
        $doctrineSystemOptionsDefault = ["system" => $defaultOptions["doctrine_options"]];
        $doctrineSystemOptions = ["system" => $this->config->get("doctrine_options")];
        $doctrineSystemOptions["system"]["base_dir"] = $baseDir;
        $doctrineSystemOptions["system"]["em_class"] = EntityManager::class;
        $doctrineSystemOptions["system"]["entity_dir"] = sprintf("%s/system/Entities", $baseDir);
        $doctrineSystemOptions["system"]["entity_namespace"] = "Entities";
        $doctrineSystemOptions["system"]["gedmo_ext"] = ["Timestampable"];
        $doctrineSystemOptions["system"]["proxy_dir"] = sprintf("%s/data/proxy/%s", $baseDir, $connectionOption);
        $doctrineSystemOptions["system"]["vendor_dir"] = sprintf("%s/vendor", $baseDir);
        $doctrineSystemOptions = ConfigFactory::fromArray($doctrineSystemOptionsDefault)->mergeValues($doctrineSystemOptions);

        $doctrineModuleOptions = new ConfigValues([]);

        if (!is_null($moduleShortName)) {
            /**
             * Build application option for module
             */
            $doctrineModuleOptionsDefault = ["module" => $defaultOptions["doctrine_options"]];
            $doctrineModuleOptions = ["module" => $this->config->get("doctrine_options")];
            $doctrineModuleOptions["module"]["base_dir"] = $moduleBaseDir;
            $doctrineModuleOptions["module"]["em_class"] = EntityManager::class;
            $doctrineModuleOptions["module"]["entity_dir"] = sprintf("%s/src/Entities", $moduleBaseDir);
            $doctrineModuleOptions["module"]["entity_namespace"] = sprintf("Modules\\%s\\Entities", $moduleShortName);
            $doctrineModuleOptions["module"]["gedmo_ext"] = ["Timestampable"];
            $doctrineModuleOptions["module"]["proxy_dir"] = sprintf("%s/data/proxy/%s", $baseDir, $connectionOption);
            $doctrineModuleOptions["module"]["vendor_dir"] = sprintf("%s/vendor", $baseDir);
            $doctrineModuleOptions = ConfigFactory::fromArray($doctrineModuleOptionsDefault)->mergeValues($doctrineModuleOptions);

            /**
             * Create and check paths
             */
            FileHelper::init($doctrineModuleOptions->get("module.entity_dir"),
                DoctrineException::class)->isReadable();

            FileHelper::init($doctrineModuleOptions->get("module.proxy_dir"),
                DoctrineException::class)->isWritable(true);
        }

        /**
         * Merge application options
         * Important! If no module controller is currently active, the system options are used for the module options
         */
        $doctrineOptions = ["doctrine_options" => [
            "system" => $doctrineSystemOptions->get("system"),
            "module" => $doctrineModuleOptions->get("module", $doctrineSystemOptions->get("system"))
        ]];

        $doctrineOptions = ConfigFactory::fromArray($doctrineOptions);

        /**
         * Create and check paths
         */
        FileHelper::init($doctrineOptions->get("doctrine_options.system.entity_dir"),
            DoctrineException::class)->isReadable();

        FileHelper::init($doctrineOptions->get("doctrine_options.system.proxy_dir"),
            DoctrineException::class)->isWritable(true);

        /**
         * Finished
         */
        $this->configValues = ConfigValues::fromConfigValues($connectionOptions)->merge($doctrineOptions);
    }

    /**
     * @return array
     */
    public final function getOptionsDefault(): array
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
            /**
             * Several database connections can be used
             * @see DoctrineService::getEntityManager()
             */
            "connection_options" => [
                "connection_option" => "default",
                "default" => [
                    "driver" => "pdo_sqlite",
                    "path" => sprintf("%s/system/db.sqlite", $baseDir),
                    "charset" => "UTF-8",
                    "prefix" => "tsi2_"
                ]
            ],
            /**
             * Only these parameters can be changed by the user. The settings are
             * adopted for the respective module as well as the system
             * @see DoctrineConfig::__construct()
             */
            "doctrine_options" => [
                "autogenerate_proxy_classes" => true,
                "debug_mode" => $isDebug,
                "cache" => $cacheDriver
            ]
        ];
    }
}