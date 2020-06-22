<?php
/**
 * MIT License
 *
 * Copyright (c) 2020 DW Web-Engineering
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Configs;


use Configula\ConfigFactory;
use Configula\ConfigValues;
use Exception;
use Exceptions\CacheException;
use Helpers\ArrayHelper;
use Helpers\DeclarationHelper;
use Helpers\DirHelper;
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
    private ?string $moduleShortName;

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
        $driverConfigPath = $cacheSystemOptions->get("system.driver.driverConfig.path");
        FileHelper::init($driverConfigPath, CacheException::class)
            ->isWritable(true);

        /**
         * Check and create directory protection
         */
        DirHelper::init($driverConfigPath)->addDirectoryProtection();

        $cacheModuleOptions = new ConfigValues([]);

        if(!empty($this->moduleShortName)){
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

                /**
                 * Check and create directory protection
                 */
                DirHelper::init($cacheModuleDir)->addDirectoryProtection();
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
        $dirName = empty($this->moduleShortName) ? "system"
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