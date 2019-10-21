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
use Helpers\CacheInitHelper;
use Interfaces\ServiceInterfaces\VendorExtensionServiceInterface;
use Managers\ModuleManager;
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
    const CACHE_SYSTEM = "system";

    /**
     * @var string
     */
    const CACHE_MODULE = "module";

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var bool
     */
    private $hasFallback = false;

    /**
     * CacheService constructor.
     * @param ModuleManager $moduleManager
     * @see ServiceManager::__construct()
     */
    public final function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param string $instance_id
     * @return ExtendedCacheItemPoolInterface
     * @throws CacheException
     */
    public final function getCacheInstance(string $instance_id): ExtendedCacheItemPoolInterface
    {
        try {
            /**
             * Init cache instance
             * @see CacheInitHelper::init()
             */
            $cache = CacheInitHelper::init(
                $this->moduleManager->getConfig(),
                $instance_id == self::CACHE_SYSTEM
                    ? $instance_id : self::CACHE_MODULE
            );

            $this->hasFallback = $cache->hasFallback();
            return $cache->getCacheInstance();

        } catch (Exception $e) {
            throw new CacheException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @return bool
     * @see CacheInitHelper::hasFallback()
     */
    public final function hasFallback()
    {
        return $this->hasFallback;
    }
}