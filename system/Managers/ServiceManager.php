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

namespace Managers;


use Exceptions\DoctrineException;
use Monolog\Logger;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Services\CacheService;
use Services\DoctrineService;
use Services\LocaleService;
use Services\LoggerService;
use Services\TemplateService;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class ServiceManager
 * @package Managers
 */
class ServiceManager
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var DoctrineService|null
     */
    private ?DoctrineService $doctrineService;

    /**
     * @var TemplateService
     */
    private TemplateService $templateService;

    /**
     * @var LoggerService
     */
    private $loggerService;

    /**
     * @var ExtendedCacheItemPoolInterface
     */
    private $cacheService;

    /**
     * @var LocaleService
     */
    private LocaleService $localeService;

    /**
     * ServiceManager constructor.
     * @param ModuleManager $moduleManager
     * @throws DoctrineException
     */
    private final function __construct(ModuleManager $moduleManager)
    {
        $this->doctrineService = DoctrineService::init($moduleManager);
        $this->loggerService = LoggerService::init($moduleManager)->getLogger();
        $this->localeService = LocaleService::init($moduleManager);
        $this->templateService = TemplateService::init($moduleManager);
        $this->cacheService = CacheService::init($moduleManager);
    }

    /**
     * @param ModuleManager $moduleManager
     * @return ServiceManager|null
     * @throws DoctrineException
     */
    public static final function init(ModuleManager $moduleManager): ?ServiceManager
    {
        if (is_null(self::$instance) || serialize($moduleManager) !== self::$instanceKey) {
            self::$instance = new self($moduleManager);
            self::$instanceKey = serialize($moduleManager);
        }

        return self::$instance;
    }

    /**
     * @return TemplateService|null
     */
    public final function getTemplateService(): ?TemplateService
    {
        return $this->templateService;
    }

    /**
     * @return DoctrineService|null
     */
    public final function getDoctrineService(): ?DoctrineService
    {
        return $this->doctrineService;
    }

    /**
     * @return Logger|null
     */
    public final function getLoggerService(): ?Logger
    {
        return $this->loggerService;
    }

    /**
     * @return CacheService|null
     */
    public final function getCacheService(): ?CacheService
    {
        return $this->cacheService;
    }

    /**
     * @return LocaleService
     */
    public final function getLocaleService(): LocaleService
    {
        return $this->localeService;
    }
}