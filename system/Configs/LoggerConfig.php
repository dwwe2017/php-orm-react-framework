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
use Exceptions\LoggerException;
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