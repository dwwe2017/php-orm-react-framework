<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Handlers;


use Configs\CoreConfig;
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
     * @var bool
     */
    private static $registered = false;

    /**
     * @var Logger|null
     */
    private $logger;

    /**
     * ErrorHandler constructor.
     * @param CoreConfig|null $config
     * @param Logger|null $logger
     */
    public function __construct(CoreConfig $config = null, Logger $logger = null)
    {
        $whoops = new Run();

        if(is_null($config))
        {
            $baseDir = sprintf("%s/../..", __DIR__);

            try
            {
                $config = CoreConfig::init($baseDir);
                $baseDir = $config->getBaseDir();
                $debugMode = $config->isDebugMode();
            }
            catch (ConfigException $e)
            {
                $debugMode = true;
            }
        }

        if($logger instanceof Logger)
        {
            $this->logger = $logger;
        }
        elseif($config instanceof CoreConfig)
        {
            $this->initLogger($config);
        }

        if (Misc::isAjaxRequest())
        {
            $jsonHandler = new JsonResponseHandler();
            $jsonHandler->addTraceToOutput(true);
            $whoops->prependHandler($jsonHandler);
        }
        elseif($debugMode)
        {
            $pretty_page_handler = new PrettyPageHandler();
            $pretty_page_handler->setApplicationRootPath($baseDir);
            $pretty_page_handler->addDataTableCallback('Details', function(Inspector $inspector)
            {
                $data = array();
                $exception = $inspector->getException();
                $data['Exception class'] = get_class($exception);
                $data['Exception code'] = $exception->getCode();
                return $data;
            });

            $whoops->prependHandler($pretty_page_handler);
            $whoops->prependHandler(function ($exception, Inspector $inspector, $whoops)
            {
                $inspector->getFrames()->map(function (Frame $frame)
                {
                    if ($function = $frame->getFunction()) {
                        $frame->addComment("This frame is within function '$function'", 'cpt-obvious');
                    }

                    return $frame;
                });
            });
        }
        else
        {
            $plain_text_handler = new PlainTextHandler();
            $plain_text_handler->addTraceToOutput(true);

            if($this->logger instanceof Logger)
            {
                $plain_text_handler->setLogger($this->logger);
                $plain_text_handler->loggerOnly(true);
            }

            $whoops->prependHandler($plain_text_handler);
        }

        if(self::$registered)
        {
            $whoops->unregister();
        }

        $whoops->register();

        self::$registered = true;
    }

    /**
     * @param CoreConfig|null $config
     * @param Logger|null $logger
     * @return ErrorHandler|null
     */
    public static function init(CoreConfig $config = null, Logger $logger = null)
    {
        if(is_null(self::$instance))
        {
            self::$instance = new ErrorHandler($config, $logger);
        }

        return self::$instance;
    }

    /**
     * @param CoreConfig $config
     */
    private function initLogger(CoreConfig $config): void
    {
        try
        {
            $this->logger = LoggerConfig::init($config, LoggerConfig::EMERGENCY);
        }
        catch (LoggerException $e)
        {
            $this->logger = null;
        }
    }
}