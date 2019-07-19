<?php

require_once 'inc/functions.inc.php';
require_once 'inc/helper.inc.php';
require_once 'inc/bootstrap.inc.php';

// Session needed for flash messages
session_start();

// Path to our index.php
$basePath = dirname(__FILE__);

$controller = $_GET['controller'] ?? 'index';
$controller = preg_replace("/[^a-z]/", "", $controller);

$action = $_GET['action'] ?? 'index';
$action = preg_replace("/[^a-z]/", "", $action);

$controllerNamespace = 'Controllers\\';
$controllerName = $controllerNamespace . ucfirst($controller) . 'Controller';

if(class_exists($controllerName))
{
    $requestController = new $controllerName($basePath);

    if(!method_exists($requestController, "run"))
    {
        $requestController = new Controllers\IndexController($basePath);
        $requestController->render404();
    }
    else
    {
        $requestController->run($action);
    }
}
else
{
    $requestController = new Controllers\IndexController($basePath);
    $requestController->render404();
}
