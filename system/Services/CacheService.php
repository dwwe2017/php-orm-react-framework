<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Services;


use Exception;
use Exceptions\CacheException;
use Helpers\CacheHelper;
use Interfaces\ServiceInterfaces\VendorExtensionServiceInterface;
use Managers\ModuleManager;
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
     * @param ModuleManager $moduleManager
     * @throws CacheException
     */
    public function __construct(ModuleManager $moduleManager)
    {
        try {
            $this->cacheInstance = CacheHelper::init(
                $moduleManager->getConfig(),
                self::CACHE_INSTANCE_ID
            );
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