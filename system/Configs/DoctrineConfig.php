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
use Interfaces\ConfigInterfaces\VendorExtensionConfigInterface;
use Webmasters\Doctrine\ORM\EntityManager;

/**
 * Class DoctrineConfig
 * @package Configs
 */
class DoctrineConfig implements VendorExtensionConfigInterface
{
    /**
     * @var self|null
     */
    public static $instance = null;

    /**
     * @var string
     */
    private static $instanceKey = "";

    /**
     * @var ConfigValues
     */
    private $config;

    /**
     * @var ConfigValues
     */
    private $configValues = null;

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
        $entityDir = $doctrineOptions->get("doctrine_options.entity_dir");

        if (!file_exists($entityDir)) {
            throw new DoctrineException(sprintf("The directory '%s' does not exist, please check the installation manually", $entityDir), E_ERROR);
        } elseif (!is_readable($entityDir)) {
            throw new DoctrineException(sprintf("The directory '%s' can not be loaded, please check the directory permissions", $entityDir), E_ERROR);
        }

        $defaultProxyDir = $doctrineOptions->get("doctrine_options.proxy_dir");

        if (!file_exists($defaultProxyDir)) {
            if (!@mkdir($defaultProxyDir, 0777, true)) {
                throw new DoctrineException(sprintf("The required proxy directory '%s' can not be created, please check the directory permissions or create it manually.", $defaultProxyDir), E_ERROR);
            }
        }

        if (!is_writable($defaultProxyDir)) {
            if (!@chmod($defaultProxyDir, 0777)) {
                throw new DoctrineException(sprintf("The required proxy directory '%s' can not be written, please check the directory permissions.", $defaultProxyDir), E_ERROR);
            }
        }

        $this->configValues = ConfigValues::fromConfigValues($connectionOptions)->merge($doctrineOptions);
    }

    /**
     * @param ConfigValues $config
     * @return ConfigValues
     * @throws DoctrineException
     */
    public static function init(ConfigValues $config): ConfigValues
    {
        if (is_null(self::$instance) || serialize(self::$instance) !== self::$instanceKey) {
            self::$instance = new self($config);
            self::$instanceKey = serialize(self::$instance);
        }

        return self::$instance->configValues;
    }

    /**
     * @return array
     */
    public function getOptionsDefault(): array
    {
        $isDebug = $this->config->get("debug_mode");
        $baseDir = $this->config->get("base_dir");
        $entityDir = sprintf("%s/system/Entities", $baseDir);
        $cacheDriver = new ArrayCache();

        if (!$isDebug) {
            if (function_exists("apc_store")) {
                $cacheDriver = new ApcuCache();
            } else {
                $filesystemCacheDirExists = true;
                $filesystemCacheDirIsWritable = true;

                $filesystemCacheDir = sprintf("%s/data/cache/doctrine", $baseDir);

                if (!file_exists($filesystemCacheDir)) {
                    $filesystemCacheDirExists = @mkdir($filesystemCacheDir, 0777, true);
                }

                if (!is_writable($filesystemCacheDir)) {
                    $filesystemCacheDirIsWritable = @chmod($filesystemCacheDir, 0777);
                }

                if ($filesystemCacheDirExists && $filesystemCacheDirIsWritable) {
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
                "entity_dir" => $entityDir,
                "entity_namespace" => basename($entityDir),
                "cache" => $cacheDriver
            ]
        ];
    }
}