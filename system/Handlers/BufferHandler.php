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


use InvalidArgumentException;
use Monolog\Logger;
use Phpfastcache\Core\Item\ExtendedCacheItemInterface;
use Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class BufferHandler
 * @package Handlers
 */
class BufferHandler
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var CacheHandler
     */
    private CacheHandler $cacheHandler;

    /**
     * @var Logger
     */
    private Logger $loggerService;

    /**
     * @var object
     */
    private object $object;

    /**
     * @var int
     */
    private int $maxLifetime = 300;

    /**
     * @var ExtendedCacheItemInterface|null
     */
    private ?ExtendedCacheItemInterface $bufferItem;

    /**
     * @param CacheHandler $cacheHandler
     * @param Logger $loggerService
     * @return BufferHandler
     */
    public static function init(CacheHandler $cacheHandler, Logger $loggerService): BufferHandler
    {
        if (is_null(self::$instance) || serialize($cacheHandler).serialize($loggerService) !== self::$instanceKey) {
            self::$instance = new self($cacheHandler, $loggerService);
            self::$instanceKey = serialize($cacheHandler).serialize($loggerService);
        }

        return self::$instance;
    }

    /**
     * BufferHandler constructor.
     * @param CacheHandler $cacheHandler
     * @param Logger $loggerService
     * @example ($this->getNavigationHandler(), $cacheHandler, $loggerService)
     */
    private function __construct(CacheHandler $cacheHandler, Logger $loggerService)
    {
        $this->cacheHandler = $cacheHandler;
        $this->loggerService = $loggerService;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->getBuffered($name, $arguments);
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     * @example ($this->fromSystemCache("getRoutes", [], 60)
     */
    public function getBuffered(string $method, array $args = array())
    {
        if (!method_exists($this->object, $method)) {
            throw new InvalidArgumentException(sprintf("The specified method %s does not exist", $method));
        }

        try {
            $itemKey = session_id();
            $itemKey .= get_class($this->object);
            $itemKey .= $method;
            $itemKey .= serialize($args);

            $systemCache = $this->cacheHandler;
            $this->bufferItem = $systemCache->getItem($itemKey);

            if (!$this->bufferItem->isHit()) {
                $result = call_user_func_array([$this->object, $method], $args);
                $this->bufferItem->set($result)->expiresAfter($this->maxLifetime);
                $systemCache->save($this->bufferItem);

                return $result;
            }

            return $this->bufferItem->get();
        } catch (PhpfastcacheInvalidArgumentException $e) {
            $this->loggerService->error($e->getMessage(), $e->getTrace());
        }

        return call_user_func_array([$this->object, $method], $args);
    }

    /**
     * @param object $object
     * @return $this
     */
    public function setObject(object $object): BufferHandler
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @param mixed $seconds
     */
    public function setMaxLifetime($seconds): void
    {
        $this->maxLifetime = $seconds;
    }

    /**
     * @return int
     */
    public function getMaxLifetime(): int
    {
        return $this->maxLifetime;
    }

    /**
     * @return ExtendedCacheItemInterface|null
     */
    public function getBufferItem(): ?ExtendedCacheItemInterface
    {
        return $this->bufferItem;
    }
}