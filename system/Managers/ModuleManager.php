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
     * @var string
     */
    private $moduleName = "";

    /**
     * @var string
     */
    private $moduleBaseDir = "";

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
     */
    private final function __construct(AbstractBase $controllerInstance)
    {
        $this->baseDir = $controllerInstance->getBaseDir();
        $this->moduleName = get_class($controllerInstance);
        $this->moduleConfig = new ConfigValues([]);

        if ($this->isModule()) {
            $this->moduleBaseDir = sprintf("%s/modules/%s", $this->getBaseDir(), $this->getModuleShortName());
            $moduleConfigPath = sprintf("%s/config", $this->moduleBaseDir);

            if (file_exists($moduleConfigPath) && is_readable($moduleConfigPath)) {
                $this->moduleConfig = ConfigFactory::loadSingleDirectory($moduleConfigPath);
            }
        }

        $this->defaultConfig = DefaultConfig::init($this);
        $this->templateConfig = TemplateConfig::init($this->defaultConfig);
        $this->doctrineConfig = DoctrineConfig::init($this->defaultConfig);
        $this->loggerConfig = LoggerConfig::init($this->defaultConfig);
        $this->cacheConfig = CacheConfig::init($this->defaultConfig);

        $this->config = $this->moduleConfig
            ->merge($this->defaultConfig
                ->getConfigValues())
            ->merge($this->templateConfig)
            ->merge($this->doctrineConfig)
            ->merge($this->loggerConfig)
            ->merge($this->cacheConfig);
    }

    /**
     * @param AbstractBase $controllerInstance
     * @return ModuleManager|null
     */
    public static final function init(AbstractBase $controllerInstance)
    {
        if (is_null(self::$instance) || serialize($controllerInstance) !== self::$instanceKey) {
            self::$instance = new self($controllerInstance);
            self::$instanceKey = serialize($controllerInstance);
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
        return strcasecmp(substr($this->getModuleName(), 0, 8), "Modules\\") === 0;
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
        return preg_replace('/^([A-Za-z]+\\\)+/', '', $this->getModuleName()); // i.e. PublicController
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
    public final function getModuleBaseDir(): string
    {
        return $this->moduleBaseDir;
    }
}