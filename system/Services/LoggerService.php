<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Services;


use Configula\ConfigValues;
use Controllers\AbstractBase;
use Exceptions\LoggerException;
use Exception;
use Interfaces\ServiceInterfaces\VendorExtensionServiceInterface;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerService implements VendorExtensionServiceInterface
{
    /**
     * @var self|null
     */
    public static $instance = null;

    /**
     * @var string
     */
    private static $instanceKey = "";

    /**
     * @var Logger
     */
    private $logger;

    /**
     * LoggerService constructor.
     * @param ConfigValues $config
     * @param AbstractBase|null $controllerInstance
     * @throws LoggerException
     */
    public function __construct(ConfigValues $config, AbstractBase $controllerInstance = null)
    {
        $logDir = $config->get("logger_options.log_dir");
        $logFile = sprintf("%s/%s.log", $logDir, date("Y_m_d"));
        $logLevel = $config->get("logger_options.log_level");

        $application = "tsi";

        try {
            $this->logger = new Logger(strtoupper($application));
            $this->logger->pushHandler(new StreamHandler($logFile, $logLevel));
            $this->logger->pushHandler(new FirePHPHandler($logLevel));
        } catch (Exception $e) {
            throw new LoggerException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param ConfigValues $config
     * @param AbstractBase|null $controllerInstance
     * @return Logger
     * @throws LoggerException
     */
    public static function init(ConfigValues $config, AbstractBase $controllerInstance = null)
    {
        if (is_null(self::$instance) || serialize(self::$instance) !== self::$instanceKey) {
            self::$instance = new self($config, $controllerInstance);
            self::$instanceKey = serialize(self::$instance);
        }

        return self::$instance->logger;
    }
}