<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Managers;


use Configula\ConfigValues;
use Controllers\AbstractBase;
use Exceptions\DoctrineException;
use Exceptions\LoggerException;
use Exceptions\CacheException;
use Services\CacheService;
use Services\DoctrineService;
use Services\LoggerService;
use Services\TemplateService;

/**
 * Class ServiceManager
 * @package Managers
 */
class ServiceManager
{
    /**
     * @var self|null
     */
    private static $instance = null;

    /**
     * @var string
     */
    private static $instanceKey = "";

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var DoctrineService
     */
    private $doctrineService;

    /**
     * @var TemplateService
     */
    private $templateService;

    /**
     * @var LoggerService
     */
    private $loggerService;

    /**
     * @var
     */
    private $cacheService;

    /**
     * @var bool
     */
    private $cacheServiceFallback = false;

    /**
     * @var AbstractBase
     */
    private $controllerInstance;

    /**
     * ServiceManager constructor.
     * @param ModuleManager $moduleManager
     * @throws CacheException
     * @throws DoctrineException
     * @throws LoggerException
     */
    protected function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
        $this->controllerInstance = $this->moduleManager;

        $config = $this->moduleManager->getConfig();
        $module = $this->moduleManager->getModule();

        $this->doctrineService = DoctrineService::init($config, $module);
        $this->loggerService = LoggerService::init($config, $module)->getLogger();
        $this->templateService = TemplateService::init($config, $module);

        $this->cacheService = CacheService::init($config, $module)->getCacheInstance();
        $this->setCacheServiceFallback($config);
    }

    /**
     * @param ModuleManager $moduleManager
     * @return ServiceManager|null
     * @throws CacheException
     * @throws DoctrineException
     * @throws LoggerException
     */
    public static function init(ModuleManager $moduleManager)
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
    public function getTemplateService(): ?TemplateService
    {
        return $this->templateService;
    }

    /**
     * @return null
     */
    public function getDoctrineService()
    {
        return $this->doctrineService;
    }

    /**
     * @return null
     */
    public function getLoggerService()
    {
        return $this->loggerService;
    }

    /**
     * @return bool
     */
    public function isCacheServiceFallback(): bool
    {
        return $this->cacheServiceFallback;
    }

    /**
     * @param ConfigValues $config
     */
    public function setCacheServiceFallback(ConfigValues $config): void
    {
        $this->cacheServiceFallback = !(strcasecmp(
            $this->cacheService->getDriverName(),
            $config->get("cache_options.driver.driverName",
                ConfigValues::NOT_SET)
            ) === 0
        );
    }
}