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
use Configula\ConfigValues;
use Exceptions\ConfigException;
use Monolog\Logger;
use Services\LoggerService;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;
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
    use InstantiationStaticsUtilTrait;

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
     * @param ConfigValues|null $config
     * @param Logger|null $logger
     */
    public function __construct(ConfigValues $config = null, Logger $logger = null)
    {
        $whoops = new Run();
        $debugMode = false;
        $baseDir = realpath(sprintf("%s/../..", __DIR__));

        if (is_null($config)) {
            try {
                $config = DefaultConfig::init($baseDir);
                $baseDir = $config->get("base_dir");
                $debugMode = $config->get("debug_mode");
            } catch (ConfigException $e) {
                $debugMode = true;
            }
        }

        if ($logger instanceof Logger) {
            $this->logger = $logger;
        } elseif ($config instanceof ConfigValues) {
            $this->initLogger($config);
        }

        if (Misc::isAjaxRequest()) {
            $jsonHandler = new JsonResponseHandler();
            if($debugMode){
                $jsonHandler->addTraceToOutput(true);
            }
            $whoops->prependHandler($jsonHandler);
        } elseif ($debugMode) {
            $pretty_page_handler = new PrettyPageHandler();
            $pretty_page_handler->setApplicationRootPath($baseDir);
            $pretty_page_handler->addDataTableCallback('Details', function (Inspector $inspector) {
                $data = array();
                $exception = $inspector->getException();
                $data['Exception class'] = get_class($exception);
                $data['Exception code'] = $exception->getCode();

                if($this->logger instanceof Logger){
                    $this->logger->error($exception->getMessage());
                }

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
     * @param ConfigValues|null $config
     * @param Logger|null $logger
     * @return ErrorHandler|null
     */
    public static function init(ConfigValues $config = null, Logger $logger = null)
    {
        if (is_null(self::$instance) || serialize($config) !== self::$instanceKey) {
            self::$instance = new self($config, $logger);
            self::$instanceKey = serialize($config);
        }

        return self::$instance;
    }

    /**
     * @param ConfigValues $config
     */
    private function initLogger(ConfigValues $config): void
    {
        try {
            $this->logger = LoggerService::init(LoggerConfig::init($config));
        } catch (LoggerException $e) {
            $this->logger = null;
        }
    }
}