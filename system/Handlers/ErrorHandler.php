<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Handlers;


use Configs\DefaultConfig;
use Configs\LoggerConfig;
use Exceptions\ConfigException;
use Monolog\Logger;
use Whoops\Exception\Frame;
use Whoops\Exception\Inspector;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Whoops\Util\Misc;
use Exceptions\LoggerException;

/**
 * Class ErrorHandler
 * @package Handlers
 */
class ErrorHandler
{
    /**
     * @var ErrorHandler|null
     */
    private static $instance;

    /**
     * @var string
     */
    private static $instance_key = "";

    /**
     * @var bool
     */
    private static $registered = false;

    /**
     * @var Logger|null
     */
    private $logger;

    /**
     * ErrorHandler constructor.
     * @param DefaultConfig|null $config
     * @param Logger|null $logger
     */
    public function __construct(DefaultConfig $config = null, Logger $logger = null)
    {
        $whoops = new Run();
        $debugMode = false;
        $baseDir = sprintf("%s/../..", __DIR__);

        if (!is_null($config)) {
            try {
                $config = DefaultConfig::init($baseDir);
                $baseDir = $config->getBaseDir();
                $debugMode = $config->isDebugMode();
            } catch (ConfigException $e) {
                $debugMode = true;
                $baseDir = sprintf("%s/../..", __DIR__);
            }
        }

        if ($logger instanceof Logger) {
            $this->logger = $logger;
        } elseif ($config instanceof DefaultConfig) {
            $this->initLogger($config);
        }

        if (Misc::isAjaxRequest()) {
            $jsonHandler = new JsonResponseHandler();
            $jsonHandler->addTraceToOutput(true);
            $whoops->prependHandler($jsonHandler);
        } elseif ($debugMode) {
            $pretty_page_handler = new PrettyPageHandler();
            $pretty_page_handler->setApplicationRootPath($baseDir);
            $pretty_page_handler->addDataTableCallback('Details', function (Inspector $inspector) {
                $data = array();
                $exception = $inspector->getException();
                $data['Exception class'] = get_class($exception);
                $data['Exception code'] = $exception->getCode();
                return $data;
            });

            $whoops->prependHandler($pretty_page_handler);
            $whoops->prependHandler(function ($exception, Inspector $inspector, $whoops) {
                $inspector->getFrames()->map(function (Frame $frame) {
                    if ($function = $frame->getFunction()) {
                        $frame->addComment("This frame is within function '$function'", 'cpt-obvious');
                    }

                    return $frame;
                });
            });
        } else {

            $errorTpl = sprintf("%/templates/Handlers/errors/whoops.php", $baseDir);
            $plain_text_handler = new PlainTextHandler();
            $plain_text_handler->addTraceToOutput(true);
            $plain_text_handler->setTemplate($errorTpl);

            if ($this->logger instanceof Logger) {
                $plain_text_handler->setLogger($this->logger);
            }

            $whoops->prependHandler($plain_text_handler);
        }

        if (self::$registered) {
            $whoops->unregister();
        }

        $whoops->register();

        self::$registered = true;
    }

    /**
     * @param DefaultConfig|null $config
     * @param Logger|null $logger
     * @return ErrorHandler|null
     */
    public static function init(DefaultConfig $config = null, Logger $logger = null)
    {
        if (is_null(self::$instance) || serialize(self::$instance) !== self::$instance_key) {
            self::$instance = new ErrorHandler($config, $logger);
            self::$instance_key = serialize(self::$instance_key);
        }

        return self::$instance;
    }

    /**
     * @param DefaultConfig $config
     */
    private function initLogger(DefaultConfig $config): void
    {
        try {
            $this->logger = LoggerConfig::init($config, LoggerConfig::EMERGENCY);
        } catch (LoggerException $e) {
            $this->logger = null;
        }
    }
}