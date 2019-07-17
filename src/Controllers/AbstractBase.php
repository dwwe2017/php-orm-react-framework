<?php

namespace Controllers;

use Doctrine\ORM\EntityManager;

abstract class AbstractBase
{
    protected $basePath;
    protected $context = [];
    protected $em;
    protected $template;

    /**
     * AbstractBase constructor.
     * @param string $basePath
     * @param EntityManager $em
     */
    public function __construct(string $basePath, EntityManager $em)
    {
        $this->basePath = $basePath;
        $this->em = $em;
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
     * @return string|null
     */
    protected function getControllerShortName(): ?string
    {
        $className = get_class($this); // i.e. Controllers\IndexController or Controllers\Backend\IndexController

        return preg_replace('/^([A-Za-z]+\\\)+/', '', $className); // i.e. IndexController
    }

    /**
     * @return EntityManager|null
     */
    protected function getEntityManager(): ?EntityManager
    {
        return $this->em;
    }

    /**
     * @param string $template
     * @param string|null $controller
     */
    protected function setTemplate(string $template, ?string $controller = null): void
    {
        if (empty($controller)) {
            $controller = $this->getControllerShortName();
        }

        $this->template = $controller . '/' . $template . '.tpl.php';
    }

    /**
     * @return string|null
     */
    protected function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @param $key
     * @param $value
     */
    protected function addContext($key, $value): void
    {
        $this->context[$key] = $value;
    }

    /**
     * @param $message
     */
    protected function setMessage(string $message): void
    {
        $_SESSION['message'] = $message; // Set flash message
    }

    /**
     * @return string|null
     */
    protected function getMessage(): ?string
    {
        $message = null;

        if (isset($_SESSION['message']))
        {
            // Read and delete flash message from session
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        return $message;
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
            $controller = new $controllerName($this->basePath, $this->em);

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

        $message = $this->getMessage(); // Get flash message
        $template = $this->getTemplate();

        require_once $this->basePath . '/templates/layout.tpl.php';
    }
}
