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
use Exceptions\LoggerException;
use Helpers\DirHelper;
use Helpers\FileHelper;
use Interfaces\ConfigInterfaces\VendorExtensionConfigInterface;
use Managers\ModuleManager;
use Monolog\Logger;
use Traits\ConfigTraits\VendorExtensionInitConfigTrait;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class LoggerConfig
 * @package Configs Revised and added options of the configuration file
 */
class LoggerConfig implements VendorExtensionConfigInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitConfigTrait;

    /**
     * LoggerConfig constructor.
     * @see ModuleManager::__construct()
     * @param DefaultConfig $defaultConfig
     */
    public final function __construct(DefaultConfig $defaultConfig)
    {
        $this->config = $defaultConfig->getConfigValues();
        $baseDir = $this->config->get("base_dir");
        $defaultOptions = $this->getOptionsDefault();

        /**
         * Build logger options
         */
        $loggerOptionsDefault = ["logger_options" => $defaultOptions["logger_options"]];
        $loggerOptions = ["logger_options" => $this->config->get("logger_options")];
        $loggerConfig = ConfigFactory::fromArray($loggerOptionsDefault)->mergeValues($loggerOptions);

        /**
         * Create and check paths
         */
        $logDir = sprintf("%s/%s", $baseDir, $loggerConfig->get("logger_options.log_dir"));
        FileHelper::init($logDir, LoggerException::class)->isWritable(true);

        /**
         * Check and create directory protection
         */
        DirHelper::init($logDir)->addDirectoryProtection();

        /**
         * Merge file values with absolute path
         */
        $loggerConfig = $loggerConfig->mergeValues([
            "logger_options" => [
                "log_dir" => $logDir,
            ]
        ]);

        /**
         * Finished
         */
        $this->configValues = $loggerConfig;
    }

    /**
     * @return array
     */
    public final function getOptionsDefault(): array
    {
        $isDebug = $this->config->get("debug_mode");
        $level = $isDebug ? Logger::DEBUG : Logger::ERROR;

        return [
            "logger_options" => [
                "debug_mode" => $isDebug,
                "log_dir" => "log",
                "log_level" => $level
            ]
        ];
    }
}