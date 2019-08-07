<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Controllers;


use Configs\DefaultConfig;
use Configs\DoctrineConfig;
use Configs\LoggerConfig;
use Configs\TemplateConfig;
use Exception;
use Exceptions\ConfigException;
use Exceptions\DoctrineException;
use Exceptions\LoggerException;
use Exceptions\MinifyCssException;
use Exceptions\MinifyJsException;
use Exceptions\TemplateException;
use Handlers\ErrorHandler;
use Handlers\MinifyCssHandler;
use Handlers\MinifyJsHandler;
use Throwable;
use Traits\ControllerTraits\AbstractBaseTrait;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class AbstractBase
 * @package Controllers
 */
abstract class AbstractBase
{
    use AbstractBaseTrait;

    /**
     * AbstractBase constructor.
     * @param string $baseDir
     * @throws ConfigException
     * @throws DoctrineException
     * @throws LoggerException
     * @throws MinifyCssException
     * @throws MinifyJsException
     * @throws TemplateException
     */
    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;
        $this->initCore();
    }

    /**
     * @throws ConfigException
     * @throws DoctrineException
     * @throws LoggerException
     * @throws MinifyCssException
     * @throws MinifyJsException
     * @throws TemplateException
     */
    private function initCore()
    {
        $this->coreConfig = DefaultConfig::init($this->getBaseDir());

        // 1. Logging
        $this->initLogger();

        // 2. Error handling, etc
        $this->initHandlers();

        // 3. Database, ORM
        $this->initDoctrine();

        // 4. Twig template engine
        $this->initTemplate();
    }

    /**
     * 1. Logging
     * @throws LoggerException
     */
    private function initLogger(): void
    {
        $this->logger = LoggerConfig::init(
            $this->getCoreConfig(),
            $this->getLogLevel()
        );
    }

    /**
     * 2. Error handling, etc
     */

    /**
     * @throws MinifyCssException
     * @throws MinifyJsException
     */
    private function initHandlers(): void
    {
        ErrorHandler::init(
            $this->getCoreConfig(),
            $this->getLogger()
        );

        $this->cssHandler = MinifyCssHandler::init(
            $this->getCoreConfig()
        );

        $this->jsHandler = MinifyJsHandler::init(
            $this->getCoreConfig()
        );
    }

    /**
     * 3. Database, ORM
     * @throws DoctrineException
     */
    private function initDoctrine(): void
    {
        $this->doctrine = DoctrineConfig::init(
            $this->getCoreConfig(),
            $this->getConnectionOption()
        );

        $this->entityManager = $this->doctrine->getEntityManager();
    }

    /**
     * 4. Twig template engine
     * @throws TemplateException
     */
    private function initTemplate(): void
    {
        $this->twig = TemplateConfig::init(
            $this->getCoreConfig(),
            $this->getModuleViewsDir()
        );
    }

    /**
     * @param string $action
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Throwable
     * @return void
     */
    public function run(string $action)
    {
        $this->addContext('action', $action);

        $methodName = $action . 'Action';

        if (method_exists($this, $methodName))
        {
            $this->setTemplate($methodName);
            $this->$methodName();
        }
        else
        {
            $this->render404();
        }

        $this->render();
    }

    /**
     *
     */
    public function render404(): void
    {
        header('HTTP/1.0 404 Not Found');
        $error = require_once sprintf("%s/templates/Handlers/errors/error404.php", $this->getBaseDir());
        die($error);
    }

    /**
     * @param $action
     * @param $controller
     */
    protected function recall(string $controller, string $action): void
    {
        $controllerName = __NAMESPACE__ . '\\' . ucfirst($controller) . 'Controller';

        if(!class_exists($controllerName))
        {
            $this->render404();
        }
        elseif(!method_exists($controller, "run"))
        {
            $this->render404();
        }
        else
        {
            $controller = new $controllerName($this->baseDir);

            $controller->run($action);
        }

        exit;
    }

    /**
     * @param string|null $module
     * @param string|null $controller
     * @param string|null $action
     */
    protected function redirect(?string $module = null, ?string $controller = null, ?string $action = null): void
    {
        $params = [];

        if(!empty($module)){
            $params[] = 'module=' . $module;
        }

        if (!empty($controller)) {
            $params[] = 'controller=' . $controller;
        }

        if (!empty($action)) {
            $params[] = 'action=' . $action;
        }

        $to = '';
        if (!empty($params)) {
            $to = '?' . implode('&', $params);
        }

        header('Location: index.php' . $to);
        exit;
    }

    /**
     * @throws Throwable
     */
    protected function render(): void
    {
        $this->cssHandler->compileAndGet();
        $this->jsHandler->compileAndGet();

        $this->addContext("message", $this->getMessage());
        $this->addContext("minified_css", $this->cssHandler->getDefaultMinifyCssFile(true));
        $this->addContext("minified_js", $this->jsHandler->getDefaultMinifyJsFile(true));

        echo $this->template->render($this->context);
    }
}
