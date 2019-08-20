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
use Handlers\CacheHelper;
use Interfaces\ServiceInterfaces\VendorExtensionServiceInterface;
use Phpfastcache\CacheManager;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Traits\ServiceTraits\VendorExtensionInitServiceTraits;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class CacheService
 * @package Services
 */
class CacheService implements VendorExtensionServiceInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitServiceTraits;

    /**
     * @var string
     */
    const CACHE_INSTANCE_ID = "result";

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var ExtendedCacheItemPoolInterface
     */
    private $cacheInstance;

    /**
     * CacheService constructor.
     * @param ConfigValues $config
     * @param AbstractBase|null $controllerInstance
     * @throws CacheException
     */
    public function __construct(ConfigValues $config, AbstractBase $controllerInstance = null)
    {
        try {
            $this->cacheInstance = CacheHelper::init($config, self::CACHE_INSTANCE_ID);
        } catch (Exception $e) {
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return ExtendedCacheItemPoolInterface
     */
    public function getCacheInstance(): ExtendedCacheItemPoolInterface
    {
        return $this->cacheInstance;
    }
}