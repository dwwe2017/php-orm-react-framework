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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
    private ModuleManager $moduleManager;

    /**
     * @var bool
     */
    private bool $hasFallback = false;

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
    public final function hasFallback(): bool
    {
        return $this->hasFallback;
    }
}