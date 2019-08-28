<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Configs;


use Configula\ConfigFactory;
use Configula\ConfigValues;
use Controllers\AbstractBase;
use Exceptions\ConfigException;
use Helpers\FileHelper;
use Interfaces\ConfigInterfaces\ApplicationConfigInterface;
use Managers\ModuleManager;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class DefaultConfig
 * @package Configs Revised and added options of the configuration file
 * @see ModuleManager::$cacheConfig
 */
class DefaultConfig implements ApplicationConfigInterface
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var string
     */
    private $baseDir = "";

    /**
     * @var string
     */
    private $moduleBaseDir = "";

    /**
     * @var string
     */
    private $moduleName = "";

    /**
     * @var string|null
     */
    private $moduleShortName = "";

    /**
     * @var ConfigValues
     */
    private $configValues = null;

    /**
     * DefaultConfig constructor.
     * @param ModuleManager $moduleManager
     */
    public function __construct(ModuleManager $moduleManager)
    {
        $this->baseDir = $moduleManager->getBaseDir();
        $this->moduleBaseDir = $moduleManager->getModuleBaseDir();
        $this->moduleName = $moduleManager->getModuleName();
        $this->moduleShortName = $moduleManager->getModuleShortName();

        $configDir = sprintf("%s/config", $this->baseDir);
        FileHelper::init($configDir, ConfigException::class)->isReadable();

        $this->configValues = ConfigFactory::loadSingleDirectory($configDir, $this->getOptionsDefault());
    }

    /**
     * @param ModuleManager $moduleManager
     * @return DefaultConfig
     */
    public static function init(ModuleManager $moduleManager): DefaultConfig
    {
        if (is_null(self::$instance) || serialize($moduleManager) !== self::$instanceKey) {
            self::$instance = new self($moduleManager);
            self::$instanceKey = serialize($moduleManager);
        }

        return self::$instance;
    }

    /**
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    /**
     * @return string
     */
    public function getModuleBaseDir(): string
    {
        return $this->moduleBaseDir;
    }

    /**
     * @return ConfigValues
     */
    public function getConfigValues(): ConfigValues
    {
        return $this->configValues;
    }

    /**
     * @return string|null
     */
    public function getModuleShortName(): ?string
    {
        return $this->moduleShortName;
    }

    /**
     * @return array
     */
    public function getOptionsDefault(): array
    {
        return [
            //General properties
            "debug_mode" => false,
            "base_dir" => $this->baseDir,
            "language" => "en_US",
            //Database configuration
            "connection_options" => [],
            //Doctrine configuration
            "doctrine_options" => [],
            //Template configuration
            "template_options" => [],
            //Logger configuration
            "logger_options" => [],
            //PhpFastCache configuration
            "cache_options" => [
                "driver" => [
                    "driverName" => "files"
                ]
            ]
        ];
    }
}