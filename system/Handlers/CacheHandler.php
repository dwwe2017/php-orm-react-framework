<?php


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