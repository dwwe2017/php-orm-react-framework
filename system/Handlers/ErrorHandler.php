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

namespace Handlers;


use Configula\ConfigValues;
use Exception;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;
use Whoops\Exception\Frame;
use Whoops\Exception\Inspector;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Whoops\Util\Misc;

/**
 * Class ErrorHandler
 * @package Handlers
 */
class ErrorHandler
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var bool
     */
    private static bool $registered = false;

    /**
     * @var Logger|null
     */
    private ?Logger $logger;

    /**
     * ErrorHandler constructor.
     * @param ConfigValues|null $config
     * @param Logger|null $logger
     */
    public final function __construct(ConfigValues $config = null, Logger $logger = null)
    {
        $whoops = new Run();
        $baseDir = realpath(sprintf("%s/../..", __DIR__));
        $debugMode = true;

        if ($config instanceof ConfigValues) {
            $baseDir = $config->get("base_dir");
            $debugMode = $config->get("debug_mode");
        }

        if ($logger instanceof Logger) {
            $this->logger = $logger;
        } else {
            try {
                $logFile = sprintf("%s/log/%s.log", $baseDir, date("Y_m_d"));
                $logLevel = $debugMode ? Logger::DEBUG : Logger::ERROR;
                $this->logger = new Logger("BOOTSTRAP");
                $this->logger->pushHandler(new StreamHandler($logFile, $logLevel));
                $this->logger->pushHandler(new FirePHPHandler($logLevel));
            } catch (Exception $e) {
                $this->logger = null;
            }
        }

        if (Misc::isAjaxRequest()) {
            $jsonHandler = new JsonResponseHandler();
            if ($debugMode) {
                $jsonHandler->addTraceToOutput(true);
            }
            $whoops->prependHandler($jsonHandler);
        } elseif ($debugMode) {
            $prettyPageHandler = new PrettyPageHandler();
            $prettyPageHandler->setApplicationRootPath($baseDir);
            $prettyPageHandler->addDataTableCallback('Details', function (Inspector $inspector) {
                $data = array();
                $exception = $inspector->getException();
                $data['Exception class'] = get_class($exception);
                $data['Exception code'] = $exception->getCode();
                $this->log($exception->getMessage(), $exception->getTrace(), Logger::ERROR);
                return $data;
            });

            $whoops->prependHandler($prettyPageHandler);
            $whoops->prependHandler(function ($exception, Inspector $inspector, $whoops) {
                $inspector->getFrames()->map(function (Frame $frame) {
                    if ($function = $frame->getFunction()) {
                        $frame->addComment(sprintf("This frame is within function '%s'", $function), 'cpt-obvious');
                    }

                    return $frame;
                });
            });
        } else {
            $errorTpl = sprintf("%/templates/Handlers/errors/whoops.php", $baseDir);
            $plainTextHandler = new PlainTextHandler();
            $plainTextHandler->addTraceToOutput(true);
            $plainTextHandler->setTemplate($errorTpl);

            if ($this->logger instanceof Logger) {
                $plainTextHandler->setLogger($this->logger);
            }

            $whoops->prependHandler($plainTextHandler);
        }

        if (self::$registered) {
            $whoops->unregister();
        }

        $whoops->register();

        self::$registered = true;
    }

    /**
     * @param ConfigValues|null $config
     * @param Logger|null $logger
     * @return ErrorHandler|null
     */
    public static final function init(ConfigValues $config = null, Logger $logger = null): ?ErrorHandler
    {
        if (is_null(self::$instance) || serialize($config) . serialize($logger) !== self::$instanceKey) {
            self::$instance = new self($config, $logger);
            self::$instanceKey = serialize($config) . serialize($logger);
        }

        return self::$instance;
    }

    /**
     * @param string $message
     * @param array $context
     * @param string $logLevel
     */
    private function log(string $message, array $context = [], string $logLevel = Logger::DEBUG): void
    {
        if ($this->logger instanceof Logger) {
            $this->logger->log($logLevel, $message, $context);
        }
    }
}