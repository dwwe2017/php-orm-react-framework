<?php

namespace Controllers;

use Doctrine\ORM\EntityManager;

abstract class AbstractBase
{
    protected $basePath;
    protected $context = [];
    protected $em;
    protected $template;

    public function __construct($basePath, EntityManager $em)
    {
        $this->basePath = $basePath;
        $this->em = $em;
    }

    public function run($action)
    {
        $this->addContext('action', $action);

        $methodName = $action . 'Action';
        $this->setTemplate($methodName);

        if (method_exists($this, $methodName)) {
            $this->$methodName();
        } else {
            $this->render404();
        }

        $this->render();
    }

    public function render404()
    {
        header('HTTP/1.0 404 Not Found');
        die('Error 404');
    }

    protected function getControllerShortName()
    {
        $className = get_class($this); // i.e. Controllers\IndexController or Controllers\Backend\IndexController

        return preg_replace('/^([A-Za-z]+\\\)+/', '', $className); // i.e. IndexController
    }

    protected function getEntityManager()
    {
        return $this->em;
    }

    protected function setTemplate($template, $controller = null)
    {
        if (empty($controller)) {
            $controller = $this->getControllerShortName();
        }

        $this->template = $controller . '/' . $template . '.tpl.php';
    }

    protected function getTemplate()
    {
        return $this->template;
    }

    protected function addContext($key, $value)
    {
        $this->context[$key] = $value;
    }

    protected function setMessage($message)
    {
        $_SESSION['message'] = $message; // Set flash message
    }

    protected function getMessage()
    {
        $message = false;
        if (isset($_SESSION['message'])) {
            // Read and delete flash message from session
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        return $message;
    }

    protected function recall($action, $controller)
    {
        $controllerName = __NAMESPACE__ . '\\' . ucfirst($controller) . 'Controller';
        $controller = new $controllerName($this->basePath, $this->em);
        $controller->run($action);
        exit;
    }

    protected function redirect($action = null, $controller = null)
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

    protected function render()
    {
        extract($this->context);

        $message = $this->getMessage(); // Get flash message
        $template = $this->getTemplate();

        require_once $this->basePath . '/templates/layout.tpl.php';
    }
}
