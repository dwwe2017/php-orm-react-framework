<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Services;


use Exception;
use Exceptions\LoggerException;
use Interfaces\ServiceInterfaces\VendorExtensionServiceInterface;
use Managers\ModuleManager;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Traits\ServiceTraits\VendorExtensionInitServiceTraits;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class LoggerService
 * @package Services
 */
class LoggerService implements VendorExtensionServiceInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitServiceTraits;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * LoggerService constructor.
     * @see ServiceManager::__construct()
     * @param ModuleManager $moduleManager
     * @throws LoggerException
     */
    public final function __construct(ModuleManager $moduleManager)
    {
        $config = $moduleManager->getConfig();
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
    public final function getLogger(): Logger
    {
        return $this->logger;
    }
}