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
use ErrorException;
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
        try {

            /**
             * Hack for triggered errors on Fallback
             */
            $errorReportingLevel = error_reporting();
            error_reporting(E_USER_ERROR);

            $defaultCacheDriverName = $config->get("cache_options.driver.driverName");
            $defaultCacheConfiguration = new ConfigurationOption($config->get("cache_options.driver.driverConfig"));

            $this->instanceCache = CacheManager::getInstance(
                $defaultCacheDriverName,
                $defaultCacheConfiguration
            );

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
}