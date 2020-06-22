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

namespace Handlers;


use Phpfastcache\Core\Item\ExtendedCacheItemInterface;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class CacheHandler
 * @package Handlers
 */
class CacheHandler
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var ExtendedCacheItemPoolInterface
     */
    private ExtendedCacheItemPoolInterface $extendedCacheItemPool;

    /**
     * @var ExtendedCacheItemInterface
     */
    private ExtendedCacheItemInterface $item;

    /**
     * CacheHandler constructor.
     * @param ExtendedCacheItemPoolInterface $extendedCacheItemPool
     */
    private function __construct(ExtendedCacheItemPoolInterface $extendedCacheItemPool)
    {
        $this->extendedCacheItemPool = $extendedCacheItemPool;
    }

    /**
     * @param ExtendedCacheItemPoolInterface $extendedCacheItemPool
     * @return CacheHandler|null
     */
    public final static function init(ExtendedCacheItemPoolInterface $extendedCacheItemPool)
    {
        if (is_null(self::$instance) || serialize($extendedCacheItemPool) !== self::$instanceKey) {
            self::$instance = new self($extendedCacheItemPool);
            self::$instanceKey = serialize($extendedCacheItemPool);
        }

        return self::$instance;
    }

    /**
     * @param string $key
     * @return ExtendedCacheItemInterface
     * @throws PhpfastcacheInvalidArgumentException
     */
    public function getItem(string $key)
    {
        return $this->extendedCacheItemPool->getItem(md5(serialize($key)));
    }

    /**
     * @param ExtendedCacheItemInterface $item
     * @return bool
     */
    public function save(ExtendedCacheItemInterface $item)
    {
        return $this->extendedCacheItemPool->save($item);
    }
}