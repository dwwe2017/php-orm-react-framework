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
use Exceptions\CacheException;
use Helpers\ArrayHelper;
use Helpers\DeclarationHelper;
use Helpers\FileHelper;
use Interfaces\ConfigInterfaces\VendorExtensionConfigInterface;
use Phpfastcache\Config\Config;
use Phpfastcache\Config\ConfigurationOption;
use Traits\ConfigTraits\VendorExtensionInitConfigTrait;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class CacheConfig
 * @package Configs
 */
class CacheConfig implements VendorExtensionConfigInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitConfigTrait;

    /**
     *
     */
    const DEV_CACHE_DRIVERS = [
        "devfalse", "devtrue", "devnull"
    ];

    /**
     *
     */
    const MEMORY_CACHE_DRIVERS = [
        "apc", "apcu", "memcache", "memcached", "memstatic", "predis", "redis", "wincache", "xcache", "Zend Memory Cache"
    ];

    /**
     *
     */
    const NOSQL_CACHE_DRIVERS = [
        "cassandra", "couchbase", "couchdb", "leveldb", "mongodb", "riak", "ssdb"
    ];

    /**
     *
     */
    const FILE_CACHE_DRIVERS = [
        "files", "zenddisk"
    ];

    /**
     *
     */
    const CACHE_MANAGER_CONFIGS = [
        "memcache" => ["class" => "\Phpfastcache\Drivers\Memcache\Config",
            "host" => true, "port" => true, "db" => false, "user" => false, "pwd" => false, "timeout" => false,
            "ssl" => false, "persistent" => true, "compression" => true,
            "defaults" => ["127.0.0.1", 11211, false, false, 0, "", "", "", false]],
        "cassandra" => ["class" => "\Phpfastcache\Drivers\Cassandra\Config",
            "host" => true, "port" => true, "db" => false, "user" => true, "pwd" => true, "timeout" => true,
            "ssl" => true, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 9142, false, false, 2, "", "", "", false]],
        "couchbase" => ["class" => "\Phpfastcache\Drivers\Couchbase\Config",
            "host" => true, "port" => true, "db" => true, "user" => true, "pwd" => true, "timeout" => false,
            "ssl" => true, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 8091, false, false, 0, "", "", "default", false]],
        "couchdb" => ["class" => "\Phpfastcache\Drivers\Couchdb\Config",
            "host" => true, "port" => true, "db" => true, "user" => true, "pwd" => true, "timeout" => true,
            "ssl" => true, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 5984, false, false, 10, "", "", "default", false]],
        "memcached" => ["class" => "\Phpfastcache\Drivers\Memcached\Config",
            "host" => true, "port" => true, "db" => false, "user" => true, "pwd" => true, "timeout" => false,
            "ssl" => false, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 11211, false, false, 0, "", "", "", false]],
        "mongodb" => ["class" => "\Phpfastcache\Drivers\Mongodb\Config",
            "host" => true, "port" => true, "db" => true, "user" => true, "pwd" => true, "timeout" => true,
            "ssl" => false, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 27017, false, false, 3, "", "", "default", false]],
        "predis" => ["class" => "\Phpfastcache\Drivers\Predis\Config",
            "host" => true, "port" => true, "db" => true, "user" => false, "pwd" => true, "timeout" => true,
            "ssl" => false, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 6379, false, false, 5, "", "", "0", false]],
        "redis" => ["class" => "\Phpfastcache\Drivers\Redis\Config",
            "host" => true, "port" => true, "db" => true, "user" => false, "pwd" => true, "timeout" => true,
            "ssl" => false, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 6379, false, false, 5, "", "", "0", false]],
        "riak" => ["class" => "\Phpfastcache\Drivers\Riak\Config",
            "host" => true, "port" => true, "db" => true, "user" => false, "pwd" => false, "timeout" => false,
            "ssl" => false, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 8098, false, false, 0, "", "", "default", false]],
        "ssdb" => ["class" => "\Phpfastcache\Drivers\Ssdb\Config",
            "host" => true, "port" => true, "db" => false, "user" => false, "pwd" => true, "timeout" => true,
            "ssl" => false, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 8888, false, false, 2000, "", "", "", false]],
        "default" => ["class" => "",
            "host" => false, "port" => false, "db" => false, "user" => false, "pwd" => false, "timeout" => false,
            "ssl" => false, "persistent" => false, "compression" => true]
    ];

    /**
     * CacheConfig constructor.
     * @param ConfigValues $config
     * @throws CacheException
     */
    public function __construct(ConfigValues $config)
    {
        $this->config = $config;
        $baseDir = $this->config->get("base_dir");

        $defaultOptions = $this->getOptionsDefault();
        $cacheOptionsDefault = ["cache_options" => $defaultOptions["cache_options"]];
        $cacheOptions = ["cache_options" => $this->config->get("cache_options")];
        $cacheConfig = ConfigFactory::fromArray($cacheOptionsDefault)->mergeValues($cacheOptions);

        $cacheDir = sprintf("%s/%s", $baseDir, $cacheConfig->get("cache_options.driver.driverConfig.path", false));

        if ($cacheDir !== false) {
            FileHelper::init($cacheDir, CacheException::class)->isWritable(true);
            $cacheConfig = $cacheConfig->mergeValues([
                "cache_options" => ["driver" => ["driverConfig" => ["path" => $cacheDir]]]
            ]);
        }

        $this->configValues = $cacheConfig;
    }

    /**
     * @return array
     * @throws CacheException
     */
    public function getOptionsDefault(): array
    {
        $isDebug = $this->config->get("debug_mode");
        $cacheDir = "data/cache/result";

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
            $this->getFallbackDriverConfig($this->isFileCacheDriver($this->config->get("cache_options.driver.driverName")))
        )->getArray();

        return [
            "cache_options" => [
                "debug_mode" => $isDebug,
                "driver" => $driver
            ]
        ];
    }

    /**
     * @param bool $fallbackFallback
     * @return array
     * @throws CacheException
     */
    private function getFallbackDriverConfig($fallbackFallback = false)
    {
        $baseDir = $this->config->get("base_dir");
        $cacheDir = sprintf("%s/data/cache/result", $baseDir);
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
                 * Set Fallback for Fallback to simple Memory
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
        } catch (\Exception $e) {
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string|null $name
     * @return bool
     */
    private function isFileCacheDriver(?string $name): bool
    {
        return in_array($name, self::FILE_CACHE_DRIVERS);
    }
}