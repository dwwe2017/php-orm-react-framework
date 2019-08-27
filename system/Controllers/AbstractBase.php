<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Controllers;


use Exceptions\MinifyCssException;
use Exceptions\MinifyJsException;
use Handlers\ErrorHandler;
use Handlers\MinifyCssHandler;
use Handlers\MinifyJsHandler;
use Helpers\AbsolutePathHelper;
use Managers\ModuleManager;
use Managers\ServiceManager;
use Throwable;
use Traits\ControllerTraits\AbstractBaseTrait;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class Config
 * @package Controllers
 */
abstract class AbstractBase
{
    use AbstractBaseTrait;

    /**
     * AbstractBase constructor.
     * @param string $baseDir
     * @throws MinifyCssException
     * @throws MinifyJsException
     */
    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;

        $this->initModule();
        $this->initServices();
        $this->initHelpers();
        $this->initHandlers();
    }

    /**
     *
     */
    private function initModule()
    {
        $this->moduleManager = ModuleManager::init($this);
        $this->config = $this->getModuleManager()->getConfig();
    }

    /**
     *
     */
    private function initServices()
    {
        $this->serviceManager = ServiceManager::init($this->getModuleManager());
        $this->loggerService = $this->getServiceManager()->getLoggerService();
        $this->doctrineService = $this->getServiceManager()->getDoctrineService();
        $this->templateService = $this->getServiceManager()->getTemplateService();
    }

    /**
     * @throws MinifyCssException
     * @throws MinifyJsException
     */
    private function initHandlers(): void
    {
        ErrorHandler::init($this->getConfig(),
            $this->loggerService
        );

        $this->cssHandler = MinifyCssHandler::init(
            $this->getConfig()
        );

        $this->jsHandler = MinifyJsHandler::init(
            $this->getConfig()
        );
    }

    /**
     *
     */
    private function initHelpers(): void
    {
        $this->absolutePathHelper = AbsolutePathHelper::init($this->getBaseDir());
    }

    /**
     * @param string $action
     * @return void
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Throwable
     * @throws LoaderError
     */
    public function run(string $action)
    {
        $this->addContext('action', $action);

        $methodName = $action . 'Action';

        if (method_exists($this, $methodName)) {
            $this->setTemplate($methodName);
            $this->$methodName();
        } else {
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
        /** @noinspection PhpIncludeInspection */
        $error = require_once $this->getAbsolutePathHelper()->{"templates/Handlers/errors/error404.php"};
        exit($error);
    }

    /**
     * @param string|null $module
     * @param string|null $controller
     * @param string|null $action
     */
    protected function redirect(?string $module = null, ?string $controller = null, ?string $action = null): void
    {
        $params = [];

        if (!empty($module)) {
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
