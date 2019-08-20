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
use Exceptions\LoggerException;
use Helpers\FileHelper;
use Interfaces\ConfigInterfaces\VendorExtensionConfigInterface;
use Monolog\Logger;
use Traits\ConfigTraits\VendorExtensionInitConfigTrait;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class LoggerConfig
 * @package Configs Revised and added options of the configuration file
 * @see ModuleManager::$cacheConfig
 */
class LoggerConfig implements VendorExtensionConfigInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitConfigTrait;

    /**
     * LoggerConfig constructor.
     * @param ConfigValues $config
     */
    public function __construct(ConfigValues $config)
    {
        $this->config = $config;
        $baseDir = $this->config->get("base_dir");

        $defaultOptions = $this->getOptionsDefault();
        $loggerOptionsDefault = ["logger_options" => $defaultOptions["logger_options"]];
        $loggerOptions = ["logger_options" => $this->config->get("logger_options")];
        $loggerConfig = ConfigFactory::fromArray($loggerOptionsDefault)->mergeValues($loggerOptions);

        $logDir = sprintf("%s/%s", $baseDir, $loggerConfig->get("logger_options.log_dir"));
        FileHelper::init($logDir, LoggerException::class)->isWritable(true);

        /**
         * Merge file values with absolute path
         */
        $loggerConfig = $loggerConfig->mergeValues([
            "logger_options" => [
                "log_dir" => $logDir,
            ]
        ]);

        $this->configValues = $loggerConfig;
    }

    /**
     * @return array
     */
    public function getOptionsDefault(): array
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