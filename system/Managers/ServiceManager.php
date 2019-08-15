<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Managers;


use Controllers\AbstractBase;
use Exceptions\DoctrineException;
use Exceptions\LoggerException;
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
     * @var AbstractBase
     */
    private $controllerInstance;

    /**
     * ServiceManager constructor.
     * @param ModuleManager $moduleManager
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
        $this->loggerService = LoggerService::init($config, $module);
        $this->templateService = TemplateService::init($config, $module);
    }

    /**
     * @param ModuleManager $moduleManager
     * @return ServiceManager|null
     * @throws DoctrineException
     * @throws LoggerException
     */
    public static function init(ModuleManager $moduleManager)
    {
        if (is_null(self::$instance) || serialize(self::$instance) !== self::$instanceKey) {
            self::$instance = new self($moduleManager);
            self::$instanceKey = serialize(self::$instance);
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
}