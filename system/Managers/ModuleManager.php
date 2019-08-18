<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Managers;


use Configs\CacheConfig;
use Configs\DefaultConfig;
use Configs\DoctrineConfig;
use Configs\LoggerConfig;
use Configs\TemplateConfig;
use Configula\ConfigFactory;
use Configula\ConfigValues;
use Controllers\AbstractBase;
use Exceptions\ConfigException;
use Exceptions\TemplateException;
use Exceptions\DoctrineException;
use Exceptions\LoggerException;

/**
 * Class ModuleManager
 * @package Managers
 */
class ModuleManager
{
    /**
     * @var ModuleManager|null
     */
    public static $instance = null;

    /**
     * @var string
     */
    public static $instanceKey = "";

    /**
     * @var string
     */
    private $baseDir = "";

    /**
     * @var AbstractBase|null
     */
    private $module = null;

    /**
     * @var string
     */
    private $moduleName = "";

    /**
     * @var string
     */
    private $modulePath = "";

    /**
     * @var ConfigValues
     */
    private $config;

    /**
     * @var ConfigValues
     */
    private $defaultConfig;

    /**
     * @var ConfigValues
     */
    private $templateConfig;

    /**
     * @var ConfigValues
     */
    private $doctrineConfig;

    /**
     * @var ConfigValues
     */
    private $loggerConfig;

    /**
     * @var ConfigValues
     */
    private $moduleConfig;

    /**
     * @var CacheConfig
     */
    private $cacheConfig;

    /**
     * ModuleManager constructor.
     * @param AbstractBase $controllerInstance
     * @throws ConfigException
     * @throws DoctrineException
     * @throws TemplateException
     * @throws LoggerException
     */
    protected final function __construct(AbstractBase $controllerInstance)
    {
        $this->baseDir = $controllerInstance->getBaseDir();
        $this->module = $controllerInstance;
        $this->moduleName = get_class($this->module);
        $this->moduleConfig = new ConfigValues([]);

        if ($this->isModule()) {
            $this->modulePath = sprintf("%s/modules/%s", $this->getBaseDir(), $this->getModuleShortName());
            $moduleConfigPath = sprintf("%s/config", $this->modulePath);

            if (file_exists($moduleConfigPath) && is_readable($moduleConfigPath)) {
                $this->moduleConfig = ConfigFactory::loadSingleDirectory($moduleConfigPath);
            }
        }

        $this->defaultConfig = DefaultConfig::init($this->getBaseDir());
        $this->templateConfig = TemplateConfig::init($this->defaultConfig);
        $this->doctrineConfig = DoctrineConfig::init($this->defaultConfig);
        $this->loggerConfig = LoggerConfig::init($this->defaultConfig);
        $this->cacheConfig = CacheConfig::init($this->defaultConfig);

        $this->config = $this->moduleConfig
            ->merge($this->defaultConfig)
            ->merge($this->templateConfig)
            ->merge($this->doctrineConfig)
            ->merge($this->loggerConfig)
            ->merge($this->cacheConfig);
    }

    /**
     * @param AbstractBase $controllerInstance
     * @return ModuleManager|null
     * @throws ConfigException
     * @throws DoctrineException
     * @throws LoggerException
     * @throws TemplateException
     */
    public static final function init(AbstractBase $controllerInstance)
    {
        if (is_null(self::$instance) || serialize(self::$instance) !== self::$instanceKey) {
            self::$instance = new self($controllerInstance);
            self::$instanceKey = serialize(self::$instance);
        }

        return self::$instance;
    }

    /**
     * @return string
     */
    public final function getBaseDir(): string
    {
        return $this->baseDir;
    }

    /**
     * @return string
     */
    public final function getModuleName(): string
    {
        return $this->moduleName;
    }

    /**
     * @return bool
     */
    public final function isModule(): bool
    {
        return $this->module && (strcasecmp(substr(get_class($this->module), 0, 8), "Modules\\") === 0);
    }

    /**
     * @return ConfigValues|null
     */
    public final function getConfig(): ?ConfigValues
    {
        return $this->config;
    }

    /**
     * @return string|null
     */
    public final function getControllerShortName(): ?string
    {
        return preg_replace('/^([A-Za-z]+\\\)+/', '', $this->getModuleName()); // i.e. MvcController
    }

    /**
     * @return string|null
     */
    public final function getModuleShortName(): ?string
    {
        $nameParts = $this->isModule() ? explode("\\", $this->getModuleName()) : null;  // i.e. Dashboard
        return $this->isModule() ? $nameParts[1] : null;
    }

    /**
     * @return string
     */
    public function getModulePath(): string
    {
        return $this->modulePath;
    }

    /**
     * @return AbstractBase|null
     */
    public function getModule(): ?AbstractBase
    {
        return $this->module;
    }
}