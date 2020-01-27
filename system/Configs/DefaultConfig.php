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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Configs;


use Configula\ConfigFactory;
use Configula\ConfigValues;
use Exceptions\ConfigException;
use Helpers\DirHelper;
use Helpers\FileHelper;
use Interfaces\ConfigInterfaces\ApplicationConfigInterface;
use Managers\ModuleManager;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class DefaultConfig
 * @package Configs Revised and added options of the configuration file
 * @see ModuleManager::$defaultConfig
 */
class DefaultConfig implements ApplicationConfigInterface
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var string
     */
    private string $baseDir = "";

    /**
     * @var string
     */
    private string $moduleBaseDir = "";

    /**
     * @var string
     */
    private string $moduleName = "";

    /**
     * @var string|null
     */
    private ?string $moduleShortName = "";

    /**
     * @var ConfigValues
     */
    private ?ConfigValues $configValues = null;

    /**
     * DefaultConfig constructor.
     * @see ModuleManager::__construct()
     * @param ModuleManager $moduleManager
     */
    public final function __construct(ModuleManager $moduleManager)
    {
        $this->baseDir = $moduleManager->getBaseDir();
        DirHelper::init($this->baseDir)->addDirectoryRestriction("^|index\.php|\.(js|css|gif|jpeg|jpg|png|woff|svg)", true);

        $this->moduleBaseDir = $moduleManager->getModuleBaseDir();
        DirHelper::init($this->moduleBaseDir)->addDirectoryRestriction();

        $this->moduleName = $moduleManager->getModuleName();
        $this->moduleShortName = $moduleManager->getModuleShortName();

        /**
         * Load global config files from base dir
         */
        $configDir = sprintf("%s/config", $this->baseDir);
        FileHelper::init($configDir, ConfigException::class)->isReadable();

        /**
         * Check and create directory protection
         */
        DirHelper::init($configDir)->addDirectoryProtection();

        $this->configValues = ConfigFactory::loadSingleDirectory($configDir, $this->getOptionsDefault());
    }

    /**
     * @see ModuleManager::__construct()
     * @param ModuleManager $moduleManager
     * @return DefaultConfig
     */
    public static final function init(ModuleManager $moduleManager): DefaultConfig
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
    public final function getModuleName(): string
    {
        return $this->moduleName;
    }

    /**
     * @return string
     */
    public final function getModuleBaseDir(): string
    {
        return $this->moduleBaseDir;
    }

    /**
     * @return ConfigValues
     */
    public final function getConfigValues(): ConfigValues
    {
        return $this->configValues;
    }

    /**
     * @return string|null
     */
    public final function getModuleShortName(): ?string
    {
        return $this->moduleShortName;
    }

    /**
     * @return array
     */
    public final function getOptionsDefault(): array
    {
        return [
            //General properties
            "debug_mode" => false,
            "base_dir" => $this->baseDir,
            "language" => "en_US",
            "entry_module" => "Dashboard",
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