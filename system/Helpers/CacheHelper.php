<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Helpers;


use Configula\ConfigValues;
use Exception;
use Exceptions\CacheException;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\Exceptions\PhpfastcacheDriverCheckException;
use Phpfastcache\Exceptions\PhpfastcacheDriverException;
use Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class CacheHelper
 * @package Helpers
 */
class CacheHelper
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var ExtendedCacheItemPoolInterface
     */
    private $cacheInstance;

    /**
     * CacheHelper constructor.
     * @param ConfigValues $config
     * @param string|null $instanceId
     * @throws CacheException
     * @throws PhpfastcacheDriverCheckException
     * @throws PhpfastcacheDriverException
     * @throws PhpfastcacheDriverNotFoundException
     * @throws PhpfastcacheInvalidArgumentException
     * @throws PhpfastcacheInvalidConfigurationException
     */
    private function __construct(ConfigValues $config, ?string $instanceId = null)
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
            $defaultCacheConfiguration = new $defaultCacheDriverClass($defaultCacheDriverConfig);
        } catch (Exception $e) {

            /**
             * Filter invalid options
             * @see CacheHelper::getInvalidConfigOptions()
             */
            $invalidConfigOptions = $this->getInvalidConfigOptions($e);
            if (!empty($invalidConfigOptions)) {

                foreach ($invalidConfigOptions as $value) {
                    unset($defaultCacheDriverConfig[$value]);
                }

                /**
                 * Re-Declare Corrected Cache Configuration
                 */
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

            $this->cacheInstance = CacheManager::getInstance(
                $defaultCacheDriverName,
                $defaultCacheConfiguration,
                $instanceId
            );

        } catch (Exception $e) {

            $this->cacheInstance = CacheManager::getInstance(
                $defaultCacheConfiguration->getFallback(),
                $defaultCacheConfiguration->getFallbackConfig(),
                $instanceId
            );
        }

        /**
         * Reset reporting level
         */
        error_reporting($errorReportingLevel);
    }

    /**
     * @param ConfigValues $config
     * @param string|null $instanceId
     * @return ExtendedCacheItemPoolInterface
     * @throws CacheException
     * @throws PhpfastcacheDriverCheckException
     * @throws PhpfastcacheDriverException
     * @throws PhpfastcacheDriverNotFoundException
     * @throws PhpfastcacheInvalidArgumentException
     * @throws PhpfastcacheInvalidConfigurationException
     */
    public static function init(ConfigValues $config, ?string $instanceId = null)
    {
        if (is_null(self::$instance) || serialize($config).serialize($instanceId) !== self::$instanceKey) {
            self::$instance = new self($config, $instanceId);
            self::$instanceKey = serialize($config).serialize($instanceId);
        }

        return self::$instance->cacheInstance;
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