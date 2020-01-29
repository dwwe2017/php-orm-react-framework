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
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Exceptions\DoctrineException;
use Helpers\DeclarationHelper;
use Helpers\DirHelper;
use Helpers\FileHelper;
use Interfaces\ConfigInterfaces\VendorExtensionConfigInterface;
use Services\DoctrineService;
use Traits\ConfigTraits\VendorExtensionInitConfigTrait;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;
use Webmasters\Doctrine\ORM\EntityManager;

/**
 * Class DoctrineConfig
 * @package Configs Revised and added options of the configuration file
 * @see ModuleManager::$doctrineConfig
 */
class DoctrineConfig implements VendorExtensionConfigInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitConfigTrait;

    /**
     * DoctrineConfig constructor.
     * @param DefaultConfig $defaultConfig
     * @throws DoctrineException
     * @see ModuleManager::__construct()
     */
    public final function __construct(DefaultConfig $defaultConfig)
    {
        $this->config = $defaultConfig->getConfigValues();

        $baseDir = $this->config->get("base_dir");
        $moduleBaseDir = $defaultConfig->getModuleBaseDir();
        $moduleShortName = $defaultConfig->getModuleShortName();
        $defaultConfigPath = sprintf("%s/config/default-config.php", $this->config->get("base_dir"));
        $defaultOptions = $this->getOptionsDefault();

        /**
         * Build connection options
         */
        $connectionOptionsDefault = ["connection_options" => $defaultOptions["connection_options"]];
        $connectionOptions = ["connection_options" => $this->config->get("connection_options")];
        $connectionOptions = ConfigFactory::fromArray($connectionOptionsDefault)->mergeValues($connectionOptions);

        /**
         * Check default connection and configuration
         */
        $connectionOption = $connectionOptions->get("connection_options.connection_option");
        $connection = $connectionOptions->get(sprintf("connection_options.%s", $connectionOption), false);
        $connectionDriver = $connectionOptions->get(sprintf("connection_options.%s.driver", $connectionOption));

        /**
         * Check db connection
         */
        if (strpos($connectionDriver, "sqlite") === false) {
            if (!$connection || count($connection) < 6) {
                throw new DoctrineException(sprintf("The '%s' field of the global configuration file '%s' does not contain a valid database connection", $connectionOption, $defaultConfigPath), E_ERROR);
            }
        }

        /**
         * Build application option for system
         */
        $doctrineSystemOptionsDefault = ["system" => $defaultOptions["doctrine_options"]];
        $doctrineSystemOptions = ["system" => $this->config->get("doctrine_options")];
        $doctrineSystemOptions["system"]["base_dir"] = $baseDir;
        $doctrineSystemOptions["system"]["em_class"] = EntityManager::class;
        $doctrineSystemOptions["system"]["entity_dir"] = sprintf("%s/system/Entities", $baseDir);
        $doctrineSystemOptions["system"]["entity_namespace"] = "Entities";
        $doctrineSystemOptions["system"]["gedmo_ext"] = ["Timestampable"];
        $doctrineSystemOptions["system"]["proxy_dir"] = sprintf("%s/data/proxy/%s", $baseDir, $connectionOption);
        $doctrineSystemOptions["system"]["vendor_dir"] = sprintf("%s/vendor", $baseDir);
        $doctrineSystemOptions = ConfigFactory::fromArray($doctrineSystemOptionsDefault)->mergeValues($doctrineSystemOptions);

        $doctrineModuleOptions = new ConfigValues([]);

        if (!is_null($moduleShortName)) {
            /**
             * @internal In the event that the module does not serve its own entities, the system entities should be accessible.
             * However, there must be a clear separation, which means that once the module has its own Entities, access to the system database is taboo.
             */
            $doctrineModuleEntityDir = sprintf("%s/src/Entities", $moduleBaseDir);
            if (FileHelper::init($doctrineModuleEntityDir)->fileExists()) {
                /**
                 * Build application option for module
                 */
                $doctrineModuleOptionsDefault = ["module" => $defaultOptions["doctrine_options"]];
                $doctrineModuleOptions = ["module" => $this->config->get("doctrine_options")];
                $doctrineModuleOptions["module"]["base_dir"] = $moduleBaseDir;
                $doctrineModuleOptions["module"]["em_class"] = EntityManager::class;
                $doctrineModuleOptions["module"]["entity_dir"] = $doctrineModuleEntityDir;
                $doctrineModuleOptions["module"]["entity_namespace"] = sprintf("Modules\\%s\\Entities", $moduleShortName);
                $doctrineModuleOptions["module"]["gedmo_ext"] = ["Timestampable"];
                $doctrineModuleOptions["module"]["proxy_dir"] = sprintf("%s/data/proxy/%s", $baseDir, $connectionOption);
                $doctrineModuleOptions["module"]["vendor_dir"] = sprintf("%s/vendor", $baseDir);
                $doctrineModuleOptions = ConfigFactory::fromArray($doctrineModuleOptionsDefault)->mergeValues($doctrineModuleOptions);

                /**
                 * Create and check paths
                 */
                FileHelper::init($doctrineModuleOptions->get("module.entity_dir"),
                    DoctrineException::class)->isReadable();

                $doctrineModuleProxyDir = $doctrineModuleOptions->get("module.proxy_dir");
                FileHelper::init($doctrineModuleProxyDir,DoctrineException::class)->isWritable(true);

                /**
                 * Check and create directory protection
                 */
                DirHelper::init($doctrineModuleProxyDir)->addDirectoryProtection();
            }
        }

        /**
         * Merge application options
         * Important! If no module controller is currently active, the system options are used for the module options
         */
        $doctrineOptions = ["doctrine_options" => [
            "system" => $doctrineSystemOptions->get("system"),
            "module" => $doctrineModuleOptions->get("module", $doctrineSystemOptions->get("system"))
        ]];

        $doctrineOptions = ConfigFactory::fromArray($doctrineOptions);

        /**
         * Create and check paths
         */
        FileHelper::init($doctrineOptions->get("doctrine_options.system.entity_dir"),
            DoctrineException::class)->isReadable();

        $doctrineSystemProxyDir = $doctrineOptions->get("doctrine_options.system.proxy_dir");
        FileHelper::init($doctrineSystemProxyDir,DoctrineException::class)->isWritable(true);

        /**
         * Check and create directory protection
         */
        DirHelper::init($doctrineSystemProxyDir)->addDirectoryProtection();

        /**
         * Finished
         */
        $this->configValues = ConfigValues::fromConfigValues($connectionOptions)->merge($doctrineOptions);
    }

    /**
     * @return array
     */
    public final function getOptionsDefault(): array
    {
        $isDebug = $this->config->get("debug_mode");
        $baseDir = $this->config->get("base_dir");
        $cacheDriver = new ArrayCache();

        if (!$isDebug) {
            if (DeclarationHelper::init("apcu", null, "apcu_add")->isDeclared()) {
                $cacheDriver = new ApcuCache();
            } else {
                $filesystemCacheDir = sprintf("%s/data/cache/doctrine", $baseDir);
                if (FileHelper::init($filesystemCacheDir)->isWritable(true)) {
                    $cacheDriver = new FilesystemCache($filesystemCacheDir);
                }

                /**
                 * Check and create directory protection
                 */
                DirHelper::init($filesystemCacheDir)->addDirectoryProtection();
            }
        }

        return [
            /**
             * Several database connections can be used
             * @see DoctrineService::getEntityManager()
             */
            "connection_options" => [
                "connection_option" => "default",
                "default" => [
                    "driver" => "pdo_sqlite",
                    "path" => sprintf("%s/system/db.sqlite", $baseDir),
                    "charset" => "UTF-8",
                    "prefix" => "tsi2_"
                ]
            ],
            /**
             * Only these parameters can be changed by the user. The settings are
             * adopted for the respective module as well as the system
             * @see DoctrineConfig::__construct()
             */
            "doctrine_options" => [
                "autogenerate_proxy_classes" => true,
                "debug_mode" => $isDebug,
                "cache" => $cacheDriver
            ]
        ];
    }
}