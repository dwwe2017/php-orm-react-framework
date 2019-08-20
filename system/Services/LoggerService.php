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
use Traits\ServiceTraits\VendorExtensionInitServiceTraits;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

class LoggerService implements VendorExtensionServiceInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitServiceTraits;

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
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }
}