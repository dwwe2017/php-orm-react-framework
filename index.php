<?php

use Handlers\ErrorHandler;

require_once 'inc/functions.inc.php';
require_once 'inc/helper.inc.php';
require_once 'inc/bootstrap.inc.php';

session_start();

$module = $_GET['module'] ?? null;
$module = is_null($module) ? $module : preg_replace("/[^a-z]/", "", $module);

$controller = $_GET['controller'] ?? 'index';
$controller = preg_replace("/[^a-z]/", "", $controller);

$action = $_GET['action'] ?? 'index';
$action = preg_replace("/[^a-z]/", "", $action);

$controllerNamespace = is_null($module) ? 'Controllers\\'
    : sprintf("Modules\\%s\\Controllers\\", ucfirst($module));

$controllerName = $controllerNamespace . ucfirst($controller) . 'Controller';

if(class_exists($controllerName))
{
    $requestController = new $controllerName($baseDir);

    if(!method_exists($requestController, "run"))
    {
        $requestController = new Controllers\IndexController($baseDir);
        $requestController->render404();
    }
    else
    {
        $requestController->run($action);
    }
}
else
{
    $requestController = new Controllers\IndexController($baseDir);
    $requestController->render404();
}

