<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

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
     * @var DoctrineService
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
    public static final function init(ModuleManager $moduleManager)
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
     * @return mixed
     */
    public final function getLocaleService()
    {
        return $this->localeService;
    }
}