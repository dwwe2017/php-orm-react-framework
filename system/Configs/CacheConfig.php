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
use Exception;
use Exceptions\CacheException;
use Helpers\ArrayHelper;
use Helpers\DeclarationHelper;
use Helpers\FileHelper;
use Interfaces\ConfigInterfaces\VendorExtensionConfigInterface;
use Managers\ModuleManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Drivers\Ssdb\Config;
use Traits\ConfigTraits\VendorExtensionInitConfigTrait;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class CacheConfig
 * @package Configs Revised and added options of the configuration file
 * @see ModuleManager::$cacheConfig
 */
class CacheConfig implements VendorExtensionConfigInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitConfigTrait;

    /**
     *  @var array Array of driver names only for development
     */
    const DEV_CACHE_DRIVERS = [
        "devfalse", "devtrue", "devnull"
    ];

    /**
     *  @var array Array of memory-based driver names
     */
    const MEMORY_CACHE_DRIVERS = [
        "apc", "apcu", "memcache", "memcached", "memstatic", "predis", "redis", "wincache", "xcache", "Zend Memory Cache"
    ];

    /**
     * @var array Array of NoSql-based driver names
     */
    const NOSQL_CACHE_DRIVERS = [
        "cassandra", "couchbase", "couchdb", "leveldb", "mongodb", "riak", "ssdb"
    ];

    /**
     * @var array Array of file-based driver names
     */
    const FILE_CACHE_DRIVERS = [
        "files", "zenddisk"
    ];

    /**
     * @var array Contains the required Configuration Option class for specific drivers
     */
    const CACHE_MANAGER_CONFIG_CLASSES = [
        "memcache" => \Phpfastcache\Drivers\Memcache\Config::class,
        "cassandra" => \Phpfastcache\Drivers\Cassandra\Config::class,
        "couchbase" => \Phpfastcache\Drivers\Couchbase\Config::class,
        "couchdb" => \Phpfastcache\Drivers\Couchdb\Config::class,
        "memcached" => \Phpfastcache\Drivers\Memcached\Config::class,
        "mongodb" => \Phpfastcache\Drivers\Mongodb\Config::class,
        "predis" => \Phpfastcache\Drivers\Predis\Config::class,
        "redis" => \Phpfastcache\Drivers\Redis\Config::class,
        "riak" => \Phpfastcache\Drivers\Riak\Config::class,
        "ssdb" => Config::class,
        "default" => ConfigurationOption::class
    ];

    /**
     * @var string|null
     */
    private $moduleShortName;

    /**
     * CacheConfig constructor.
     * @see ModuleManager::__construct()
     * @param DefaultConfig $defaultConfig
     * @throws CacheException
     */
    public final function __construct(DefaultConfig $defaultConfig)
    {
        $this->config = $defaultConfig->getConfigValues();
        $this->moduleShortName = $defaultConfig->getModuleShortName();
        $baseDir = $this->config->get("base_dir");
        $cacheSystemDir = sprintf("%s/data/cache/system", $baseDir);
        $defaultOptions = $this->getOptionsDefault();

        /**
         * Build system cache options
         */
        $cacheSystemOptionsDefault = ["system" => $defaultOptions["cache_options"]];
        $cacheSystemOptions = $cacheSystemOptionsDefault;
        $cacheSystemOptions["system"]["driver"]["driverClass"] = ConfigurationOption::class;
        $cacheSystemOptions["system"]["driver"]["driverConfig"]["path"] = $cacheSystemDir;
        $cacheSystemOptions = ConfigFactory::fromArray($cacheSystemOptions);

        /**
         * Check file permissions for system cache dir
         */
        FileHelper::init($cacheSystemOptions->get("system.driver.driverConfig.path"), CacheException::class)
            ->isWritable(true);

        $cacheModuleOptions = new ConfigValues([]);

        if(!is_null($this->moduleShortName)){
            /**
             * Build module cache options
             */
            $cacheModuleOptionsDefault = ["module" => $defaultOptions["cache_options"]];
            $cacheModuleOptions = ["module" => $this->config->get("cache_options")];
            $cacheModuleOptions = ConfigFactory::fromArray($cacheModuleOptionsDefault)->mergeValues($cacheModuleOptions);
            $cacheModuleDriver = $cacheModuleOptions->get("module.driver.driverName");
            $cacheModuleClass =  $cacheModuleOptions->get("module.driver.driverClass", ConfigurationOption::class);

            if($cacheModuleClass === ConfigurationOption::class && key_exists(strtolower($cacheModuleDriver), self::CACHE_MANAGER_CONFIG_CLASSES)){
                $cacheModuleClass = self::CACHE_MANAGER_CONFIG_CLASSES[strtolower($cacheModuleDriver)];
            }

            /**
             * Check file permissions for module cache dir if necessary
             */
            $cacheModuleDir = sprintf("%s/%s", $baseDir,
                $cacheModuleOptions->get("module.driver.driverConfig.path", false)
            );

            if ($cacheModuleDir !== false) {
                FileHelper::init($cacheModuleDir, CacheException::class)->isWritable(true);
                $cacheModuleOptions = $cacheModuleOptions->mergeValues([
                    "module" => ["driver" => ["driverConfig" => ["path" => $cacheModuleDir], "driverClass" => $cacheModuleClass]]
                ]);
            }
        }

        /**
         * Merge cache options
         * Important! If no module controller is currently active, the system options are used for the module options
         */
        $cacheOptions = ["cache_options" => [
            "system" => $cacheSystemOptions->get("system"),
            "module" => $cacheModuleOptions->get("module", $cacheSystemOptions->get("system"))
        ]];

        /**
         * Finished
         */
        $this->configValues = ConfigFactory::fromArray($cacheOptions);
    }

    /**
     * @return array Returns an array with the required default options
     * @see CacheConfig::getFallbackDriverConfig()
     * @throws CacheException
     */
    public final function getOptionsDefault(): array
    {
        $isDebug = $this->config->get("debug_mode");
        $dirName = is_null($this->moduleShortName) ? "system"
            : sprintf("result/%s", strtolower($this->moduleShortName));

        $cacheDir = sprintf("data/cache/%s", $dirName);

        $driver = [
            "driverName" => "files",
            "driverConfig" => [
                "path" => $cacheDir,
                "itemDetailedDate" => true,
                "defaultKeyHashFunction" => "sha1",
                "defaultFileNameHashFunction" => "sha1",
                "autoTmpFallback" => true
            ]
        ];

        /**
         * Append configuration options for Fallback and FallbackFallback
         */
        $driver = ArrayHelper::init($driver)->append(
            $this->getFallbackDriverConfig(
                strcasecmp($this->config->get("cache_options.driver.driverName"), "files") != 0
            )
        );

        return [
            "cache_options" => [
                "debug_mode" => $isDebug,
                "driver" => $driver->getArray()
            ]
        ];
    }

    /**
     * @param bool $fallbackFallback
     * @return array Returns an array with the required default fallback options. If the file driver is not used, a fallback is also set for the fallback
     * @see CacheConfig::getOptionsDefault()
     * @throws CacheException
     */
    private function getFallbackDriverConfig($fallbackFallback = false)
    {
        $baseDir = $this->config->get("base_dir");
        $cacheDir = sprintf("%s/data/cache/system", $baseDir);
        FileHelper::init($cacheDir, CacheException::class)->isWritable(true);

        try {

            $fallbackConfig = new ConfigurationOption();

            /**
             * Set Fallback to filesystem
             */
            if ($fallbackFallback) {

                if (DeclarationHelper::init("zenddisk")->isDeclared()) {
                    $fallbackDriver = "zenddisk";
                } else {
                    $fallbackDriver = "files";
                    $fallbackConfig->setPath($cacheDir);
                    $fallbackConfig->setDefaultChmod(0777);
                }

                /**
                 * Set Fallback for Fallback to simple Memory (memstatic)
                 */
                $fallbackFallbackConfig = $this->getFallbackDriverConfig(false);
                $fallbackConfig->setFallback($fallbackFallbackConfig["driverConfig"]["fallback"]);
                $fallbackConfig->setFallbackConfig($fallbackFallbackConfig["driverConfig"]["fallbackConfig"]);

            } else {
                /**
                 * Set Fallback to simple Memory
                 */
                if (DeclarationHelper::init("zendshm")->isDeclared()) {
                    $fallbackDriver = "zendshm";
                } else {
                    $fallbackDriver = "memstatic";
                }
            }

            $fallbackConfig->setDefaultFileNameHashFunction("sha1");
            $fallbackConfig->setDefaultKeyHashFunction("sha1");
            $fallbackConfig->setItemDetailedDate(true);
            $fallbackConfig->setCompressData(true);

            return [
                "driverConfig" => [
                    "fallback" => $fallbackDriver,
                    "fallbackConfig" => $fallbackConfig
                ]
            ];
        } catch (Exception $e) {
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }
    }
}