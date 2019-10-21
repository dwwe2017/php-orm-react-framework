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
use Helpers\FileHelper;

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
    private $modulesDir = "";

    /**
     * @var string
     */
    private $moduleName = "";

    /**
     * @var string
     */
    private $entryModule = "";

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
        $this->moduleBaseDir = $this->getBaseDir();
        $this->modulesDir = sprintf("%s/modules", $this->getBaseDir());

        if ($this->isModule()) {
            $this->moduleBaseDir = sprintf("%s/%s", $this->getModulesDir(), $this->getModuleShortName());
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

        /**
         * @internal Set default modules whose index controller and index action are called when no parameters are called
         * @example The field "entry_module" => "Dashboard" in the configuration file causes the
         * link "index.php?module=dashboard&controller=index&action=index" to be called if there are no parameters
         */
        $this->entryModule = $this->getConfig()->get("entry_module", "Dashboard");

        /**
         * Correct entry point if specified module does not exist or an error exists
         */
        if(strcasecmp($this->getEntryModule(), "Dashboard") !== 0
        && !class_exists(sprintf("Modules\\%s\\Controllers\\IndexController", ucfirst($this->getEntryModule())))){
            $this->entryModule = "Dashboard";
        }
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

    /**
     * @param bool $relative
     * @return string
     */
    public final function getBaseUrl($relative = true): string
    {
        $result = $relative ? str_replace($this->getBaseDir(), "", $this->getModuleBaseDir()) : $this->getModuleBaseDir();
        //$subDir = stripos($_SERVER["REQUEST_URI"], "?") !== false ? explode("?", $_SERVER["REQUEST_URI"])[0] : $_SERVER["REQUEST_URI"];
        return $result;//str_replace("/index.php", "", sprintf("%s/%s", $subDir, $result));
    }

    /**
     * @param string $methodAction
     * @param bool $relative
     * @return string|null
     */
    public final function getMethodJsAction(string $methodAction, $relative = false)
    {
        $file = sprintf("%s/%s.js", $this->getJsAssetsPath(false), $methodAction);
        return !FileHelper::init($file)->fileExists() ? null :
            sprintf("%s/%s.js", $this->getJsAssetsPath($relative), $methodAction);
    }

    /**
     * @param bool $relative
     * @return string
     */
    public final function getJsAssetsPath($relative = false): string
    {
        return $relative ? sprintf("assets/js/%s", $this->getControllerShortName())
            : sprintf("%s/assets/js/%s", $this->getModuleBaseDir(), $this->getControllerShortName());
    }

    /**
     * @param string $methodAction
     * @param bool $relative
     * @return string|null
     */
    public final function getMethodCssAction(string $methodAction, $relative = false)
    {
        $file = sprintf("%s/%s.css", $this->getCssAssetsPath(false), $methodAction);
        return !FileHelper::init($file)->fileExists() ? null :
            sprintf("%s/%s.css", $this->getCssAssetsPath($relative), $methodAction);
    }

    /**
     * @param bool $relative
     * @return string
     */
    public final function getCssAssetsPath($relative = false): string
    {
        return $relative ? sprintf("assets/css/%s", $this->getControllerShortName())
            : sprintf("%s/assets/css/%s", $this->getModuleBaseDir(), $this->getControllerShortName());
    }

    /**
     * @return string
     */
    public function getModulesDir(): string
    {
        return $this->modulesDir;
    }

    /**
     * @return string
     */
    public function getEntryModule(): string
    {
        return lcfirst($this->entryModule);
    }
}
