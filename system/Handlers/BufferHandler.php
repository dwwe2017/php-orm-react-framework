<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2020. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

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
     * @var string
     */
    private string $itemKey = "";

    /**
     * @var ExtendedCacheItemInterface|null
     */
    private ?ExtendedCacheItemInterface $bufferItem;

    /**
     * @param CacheHandler $cacheHandler
     * @param Logger $loggerService
     * @return BufferHandler
     */
    public static function init(CacheHandler $cacheHandler, Logger $loggerService)
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
            $this->itemKey = session_id();
            $this->itemKey .= get_class($this->object);
            $this->itemKey .= $method;
            $this->itemKey .= serialize($args);

            $systemCache = $this->cacheHandler;
            $this->bufferItem = $systemCache->getItem($this->itemKey);

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

        $result = call_user_func_array([$this->object, $method], $args);
        return $result;
    }

    /**
     * @param object $object
     * @return $this
     */
    public function setObject(object $object)
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
     * @return mixed
     */
    public function getMaxLifetime()
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