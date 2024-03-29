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


use Configs\CacheConfig;
use Configs\DefaultConfig;
use Configs\DoctrineConfig;
use Configs\LoggerConfig;
use Configs\PortalConfig;
use Configs\TemplateConfig;
use Configula\ConfigFactory;
use Configula\ConfigValues;
use Controllers\AbstractBase;
use Helpers\DirHelper;
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
    public static ?ModuleManager $instance = null;

    /**
     * @var string
     */
    public static string $instanceKey = "";

    /**
     * @var string
     */
    private string $baseDir;

    /**
     * @var string
     */
    private string $modulesDir;

    /**
     * @var string
     */
    private string $moduleName;

    /**
     * @var string
     */
    private string $entryModule;

    /**
     * @var string
     */
    private string $moduleBaseDir;

    /**
     * @var ConfigValues
     */
    private ConfigValues $config;

    /**
     * @var ConfigValues
     */
    private $defaultConfig;

    /**
     * @var ConfigValues
     */
    private ConfigValues $templateConfig;

    /**
     * @var ConfigValues
     */
    private ConfigValues $doctrineConfig;

    /**
     * @var ConfigValues
     */
    private ConfigValues $loggerConfig;

    /**
     * @var ConfigValues
     */
    private ConfigValues $moduleConfig;

    /**
     * @var CacheConfig
     */
    private $cacheConfig;

    /**
     * @var ConfigValues
     */
    private ConfigValues $portalConfig;

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
        $this->portalConfig = PortalConfig::init($this->defaultConfig);

        $this->config = $this->moduleConfig
            ->merge($this->defaultConfig
                ->getConfigValues())
            ->merge($this->templateConfig)
            ->merge($this->doctrineConfig)
            ->merge($this->loggerConfig)
            ->merge($this->cacheConfig)
            ->merge($this->portalConfig);

        /**
         * @internal Set default modules whose index controller and index action are called when no parameters are called
         * @example The field "entry_module" => "Dashboard" in the configuration file causes the
         * link "index.php?module=dashboard&controller=index&action=index" to be called if there are no parameters
         */
        $this->entryModule = $this->getConfig()->get("entry_module", "Dashboard");

        /**
         * Correct entry point if specified module does not exist or an error exists
         */
        if(!class_exists(sprintf("Modules\\%s\\Controllers\\IndexController", ucfirst($this->getEntryModule())))){
            foreach (DirHelper::init($this->getModulesDir())->getScan() as $moduleDir){
                $this->entryModule = $moduleDir;
                break;
            }
        }
    }

    /**
     * @param AbstractBase $controllerInstance
     * @return ModuleManager|null
     */
    public static final function init(AbstractBase $controllerInstance): ?ModuleManager
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
    public final function getBaseUrl(bool $relative = true): string
    {
        return $relative ? str_replace($this->getBaseDir(), "", $this->getModuleBaseDir()) : $this->getModuleBaseDir();
    }

    /**
     * @param string $methodAction
     * @param bool $relative
     * @return string|null
     */
    public final function getMethodJsAction(string $methodAction, bool $relative = false): ?string
    {
        $file = sprintf("%s/%s.js", $this->getJsAssetsPath(), $methodAction);
        return !FileHelper::init($file)->fileExists() ? null :
            sprintf("%s/%s.js", $this->getJsAssetsPath($relative), $methodAction);
    }

    /**
     * @param bool $relative
     * @return string
     */
    public final function getJsAssetsPath(bool $relative = false): string
    {
        return $relative ? sprintf("assets/js/%s", $this->getControllerShortName())
            : sprintf("%s/assets/js/%s", $this->getModuleBaseDir(), $this->getControllerShortName());
    }

    /**
     * @param string $methodAction
     * @param bool $relative
     * @return string|null
     */
    public final function getMethodCssAction(string $methodAction, bool $relative = false): ?string
    {
        $file = sprintf("%s/%s.css", $this->getCssAssetsPath(), $methodAction);
        return !FileHelper::init($file)->fileExists() ? null :
            sprintf("%s/%s.css", $this->getCssAssetsPath($relative), $methodAction);
    }

    /**
     * @param bool $relative
     * @return string
     */
    public final function getCssAssetsPath(bool $relative = false): string
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
