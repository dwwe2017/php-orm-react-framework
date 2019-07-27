<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Controllers;


use Core\Bootstrap;
use Traits\AbstractBaseTrait;
use Exceptions\ConfigException, Exceptions\DoctrineException, Exceptions\TemplateException, Exception;
use View\Environment;

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
     * @throws TemplateException
     */
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->config = Bootstrap::init($basePath);

        $this->initDoctrine();
        $this->initTemplate();
    }

    /**
     * @throws DoctrineException
     */
    private function initDoctrine(): void
    {
        $this->doctrine = \Doctrine\Bootstrap::init($this->getConfig());
        $this->entityManager = $this->doctrine->getEntityManager();
    }

    /**
     * @throws TemplateException
     */
    private function initTemplate(): void
    {
        try
        {
            $this->twig = \View\Bootstrap::init($this->getConfig());
        }
        catch (Exception $e)
        {
            //@todo | Implement logging
            throw new TemplateException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $action
     */
    public function run(string $action): void
    {
        $this->addContext('action', $action);

        $methodName = $action . 'Action';
        $this->setTemplate($methodName);

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
     *
     */
    protected function render(): void
    {
        extract($this->context);

        /** @noinspection PhpUnusedLocalVariableInspection*/
        $message = $this->getMessage(); // Get flash message
        /** @noinspection PhpUnusedLocalVariableInspection*/
        $template = $this->getTemplate();

        /** @noinspection PhpIncludeInspection */
        require_once $this->basePath . '/templates/layout.tpl.php';
    }
}
