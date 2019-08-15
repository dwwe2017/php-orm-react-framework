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
use Interfaces\ConfigInterfaces\VendorExtensionConfigInterface;
use Monolog\Logger;

/**
 * Class LoggerConfig
 * @package Configs
 */
class LoggerConfig implements VendorExtensionConfigInterface
{
    const DEBUG = Logger::DEBUG;

    const INFO = Logger::INFO;

    const NOTICE = Logger::NOTICE;

    const WARNING = Logger::WARNING;

    const ERROR = Logger::ERROR;

    const CRITICAL = Logger::CRITICAL;

    const ALERT = Logger::ALERT;

    const EMERGENCY = Logger::EMERGENCY;

    /**
     * @var self|null
     */
    public static $instance = null;

    /**
     * @var string
     */
    private static $instanceKey = "";

    /**
     * @var ConfigValues
     */
    private $config;

    /**
     * @var ConfigValues
     */
    private $configValues = null;

    /**
     * LoggerConfig constructor.
     * @param ConfigValues $config
     * @throws LoggerException
     */
    public function __construct(ConfigValues $config)
    {
        $this->config = $config;

        $defaultOptions = $this->getOptionsDefault();
        $loggerOptionsDefault = ["logger_options" => $defaultOptions["logger_options"]];
        $loggerOptions = ["logger_options" => $this->config->get("logger_options")];
        $loggerConfig = ConfigFactory::fromArray($loggerOptionsDefault)->mergeValues($loggerOptions);

        $logDir = $loggerConfig->get("logger_options.log_dir");

        if (!file_exists($logDir)) {
            if (!@mkdir($logDir, 0777, true)) {
                throw new LoggerException(sprintf("The required log directory '%s' can not be created, please check the directory permissions or create it manually.", $logDir), E_ERROR);
            }
        }

        if (!is_writable($logDir)) {
            if (!@chmod($logDir, 0777)) {
                throw new LoggerException(sprintf("The required log directory '%s' can not be written, please check the directory permissions.", $logDir), E_ERROR);
            }
        }

        $this->configValues = $loggerConfig;
    }

    /**
     * @param ConfigValues $config
     * @return ConfigValues
     * @throws LoggerException
     */
    public static function init(ConfigValues $config): ConfigValues
    {
        if (is_null(self::$instance) || self::$instanceKey !== serialize(self::$instance)) {
            self::$instance = new self($config);
            self::$instanceKey = serialize(self::$instance);
        }

        return self::$instance->configValues;
    }

    /**
     * @return array
     */
    public function getOptionsDefault(): array
    {
        $isDebug = $this->config->get("debug_mode");
        $level = $isDebug ? self::DEBUG : self::ERROR;
        $baseDir = $this->config->get("base_dir");
        $defaultLogDir = sprintf("%s/log", $baseDir);

        return [
            "logger_options" => [
                "debug_mode" => $isDebug,
                "log_dir" => $defaultLogDir,
                "log_level" => $level
            ]
        ];
    }
}