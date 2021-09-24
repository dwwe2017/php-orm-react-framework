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