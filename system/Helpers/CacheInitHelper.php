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

namespace Helpers;


use Configula\ConfigFactory;
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
class CacheInitHelper
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var ExtendedCacheItemPoolInterface
     */
    private ExtendedCacheItemPoolInterface $cacheInstance;

    /**
     * @var bool
     */
    private bool $hasFallback = false;

    /**
     * CacheHelper constructor.
     * @param ConfigValues $config
     * @param string $instanceId
     * @throws CacheException
     * @throws PhpfastcacheDriverCheckException
     * @throws PhpfastcacheDriverException
     * @throws PhpfastcacheDriverNotFoundException
     * @throws PhpfastcacheInvalidArgumentException
     * @throws PhpfastcacheInvalidConfigurationException
     */
    private function __construct(ConfigValues $config, ?string $instanceId)
    {
        $cacheOptions = ConfigFactory::fromArray(
            $config->get(sprintf("cache_options.%s", $instanceId))
        );

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
        $defaultCacheDriverName = $cacheOptions->get("driver.driverName");
        $defaultCacheDriverClass = $cacheOptions->get("driver.driverClass");
        $defaultCacheDriverConfig = $cacheOptions->get("driver.driverConfig");

        try {
            $defaultCacheConfiguration = new $defaultCacheDriverClass($defaultCacheDriverConfig);
        } catch (Exception $e) {

            /**
             * Filter invalid options
             * @see CacheInitHelper::getInvalidConfigOptions()
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

        $this->hasFallback = !(strcasecmp(
                $this->cacheInstance->getDriverName(),
                $cacheOptions->get("driver.driverName",
                    ConfigValues::NOT_SET)
            ) === 0
        );

        /**
         * Reset reporting level
         */
        error_reporting($errorReportingLevel);
    }

    /**
     * @param ConfigValues $config
     * @param string|null $instanceId
     * @return CacheInitHelper|null
     * @throws CacheException
     * @throws PhpfastcacheDriverCheckException
     * @throws PhpfastcacheDriverException
     * @throws PhpfastcacheDriverNotFoundException
     * @throws PhpfastcacheInvalidArgumentException
     * @throws PhpfastcacheInvalidConfigurationException
     */
    public static final function init(ConfigValues $config, ?string $instanceId = null)
    {
        if (is_null(self::$instance) || serialize($config).serialize($instanceId) !== self::$instanceKey) {
            self::$instance = new self($config, $instanceId);
            self::$instanceKey = serialize($config).serialize($instanceId);
        }

        return self::$instance;
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

    /**
     * @return bool
     */
    public final function hasFallback(): bool
    {
        return $this->hasFallback;
    }

    /**
     * @return ExtendedCacheItemPoolInterface
     */
    public final function getCacheInstance(): ExtendedCacheItemPoolInterface
    {
        return $this->cacheInstance;
    }
}