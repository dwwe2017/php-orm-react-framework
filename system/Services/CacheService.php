<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Services;


use Configula\ConfigValues;
use Controllers\AbstractBase;
use Exception;
use Exceptions\CacheException;
use Interfaces\ServiceInterfaces\VendorExtensionServiceInterface;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class CacheService
 * @package Services
 */
class CacheService implements VendorExtensionServiceInterface
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var ExtendedCacheItemPoolInterface
     */
    private $instanceCache;

    /**
     * CacheService constructor.
     * @param ConfigValues $config
     * @param AbstractBase|null $controllerInstance
     * @throws CacheException
     */
    public function __construct(ConfigValues $config, AbstractBase $controllerInstance = null)
    {
        /**
         * @var $defaultCacheDriverName string
         * @var $defaultCacheDriverClass string
         * @var $defaultCacheConfiguration ConfigurationOption
         * |\Phpfastcache\Drivers\Memcache\Config|\Phpfastcache\Drivers\Cassandra\Config
         * |\Phpfastcache\Drivers\Couchbase\Config|\Phpfastcache\Drivers\Couchdb\Config
         * |\Phpfastcache\Drivers\Memcached\Config|\Phpfastcache\Drivers\Mongodb\Config
         * |\Phpfastcache\Drivers\Predis\Config|\Phpfastcache\Drivers\Redis\Config
         * |\Phpfastcache\Drivers\Riak\Config|\Phpfastcache\Drivers\Ssdb\Config
         */
        $defaultCacheDriverName = $config->get("cache_options.driver.driverName");
        $defaultCacheDriverClass = $config->get("cache_options.driver.driverClass");
        $defaultCacheDriverConfig = $config->get("cache_options.driver.driverConfig");

        try {

            try {
                $defaultCacheConfiguration = new $defaultCacheDriverClass($defaultCacheDriverConfig);
            } catch (Exception $e) {

                /**
                 * Filter invalid options
                 */
                $invalidConfigOptions = $this->getInvalidConfigOptions($e);
                if (!empty($invalidConfigOptions)) {

                    foreach ($invalidConfigOptions as $value) {
                        unset($defaultCacheDriverConfig[$value]);
                    }

                    $defaultCacheConfiguration = new $defaultCacheDriverClass($defaultCacheDriverConfig);

                } else {
                    throw new CacheException($e->getMessage(), $e->getCode(), $e);
                }
            }

            /**
             * Hack for triggered errors on Fallback
             */
            $errorReportingLevel = error_reporting();
            error_reporting(E_USER_ERROR);

            try {

                $this->instanceCache = CacheManager::getInstance(
                    $defaultCacheDriverName,
                    $defaultCacheConfiguration
                );

            } catch (Exception $e) {

                $this->instanceCache = CacheManager::getInstance(
                    $defaultCacheConfiguration->getFallback(),
                    $defaultCacheConfiguration->getFallbackConfig()
                );
            }

            /**
             * Reset reporting level
             */
            error_reporting($errorReportingLevel);

        } catch (Exception $e) {
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param ConfigValues $config
     * @param AbstractBase|null $controllerInstance
     * @return ExtendedCacheItemPoolInterface
     * @throws CacheException
     */
    public static function init(ConfigValues $config, AbstractBase $controllerInstance = null)
    {
        if (is_null(self::$instance) || serialize(self::$instance) !== self::$instanceKey) {
            self::$instance = new self($config, $controllerInstance);
            self::$instanceKey = serialize(self::$instance);
        }

        return self::$instance->instanceCache;
    }

    /**
     * @param Exception $e
     * @return array
     */
    private function getInvalidConfigOptions(Exception $e): array
    {
        $result = [];
        $message = $e->getMessage();
        if (strpos($e->getMessage(), ":") !== false) {
            $messageParts = explode(":", $message);
            if (count($messageParts) > 1) {
                $result = array_map(
                    "trim", explode(",", $messageParts[1])
                );
            }
        }

        return $result;
    }
}