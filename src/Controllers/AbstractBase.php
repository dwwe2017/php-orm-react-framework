<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Controllers;


use Bootstrap\Bootstrap;
use Traits\AbstractBaseTrait;
use Exceptions\BootstrapException;

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
     * @throws BootstrapException
     */
    public function __construct(string $basePath)
    {
        $this->init($basePath);
    }

    /**
     * @param string $basePath
     * @throws BootstrapException
     */
    private function init(string $basePath)
    {
        try
        {
            $this->basePath = $basePath;

            $this->bootstrap = Bootstrap::init(
                $this->getBasePath(),
                $this->getConnectionOption()
            );

            $this->entityManager = $this->bootstrap->getEntityManager();
        }
        catch (BootstrapException $e)
        {
            //@todo | Implement logging
            throw $e;
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
