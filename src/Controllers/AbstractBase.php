<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Controllers;


use Configs\CoreConfig;
use Configs\DoctrineConfig;
use Configs\LoggerConfig;
use Configs\TemplateConfig;
use Traits\AbstractBaseTrait;
use Exceptions\ConfigException, Exceptions\DoctrineException, Exceptions\TemplateException, Exceptions\LoggerException;

/**
 * Class AbstractBase
 * @package Controllers
 */
abstract class AbstractBase
{
    use AbstractBaseTrait;

    /**
     * AbstractBase constructor.
     * @param string $basePath
     * @throws ConfigException
     * @throws DoctrineException
     * @throws LoggerException
     * @throws TemplateException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->coreConfig = CoreConfig::init($basePath);

        $this->initDoctrine();
        $this->initTemplate();
        $this->initLogger();
    }

    /**
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
     * @throws TemplateException
     */
    private function initTemplate(): void
    {
        $this->twig = TemplateConfig::init(
            $this->getCoreConfig()
        );
    }

    /**
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
     * @param string $action
     * @throws \Throwable
     */
    public function run(string $action): void
    {
        $this->addContext('action', $action);

        $methodName = $action . 'Action';
        $this->setTemplate($methodName);

        $this->template = $this->twig->getTemplateWrapper($this->getTemplatePath());

        if (method_exists($this, $methodName))
        {
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
        die('Error 404');
    }

    /**
     * @param $action
     * @param $controller
     */
    protected function recall(string $action, string $controller): void
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
            $controller = new $controllerName($this->basePath);

            $controller->run($action);
        }

        exit;
    }

    /**
     * @param string|null $action
     * @param string|null $controller
     */
    protected function redirect(?string $action = null, ?string $controller = null): void
    {
        $params = [];

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
     * @throws \Throwable
     */
    protected function render(): void
    {
        $this->addContext("message", $this->getMessage());
        echo $this->template->render($this->context);
    }
}
