<?php
/**
 * MIT License
 *
 * Copyright (c) 2020 DW Web-Engineering
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

require_once 'inc/functions.inc.php';
require_once 'inc/helper.inc.php';
require_once 'inc/bootstrap.inc.php';

session_start();

$module = $_GET['module'] ?? null;
$module = $module && strpos($module, "/") ? explode("/", $module)[0] : $module;
$module = is_null($module) ? $module : htmlentities(lcfirst($module));

$controller = $_GET['controller'] ?? (!is_null($module) ? 'index' : 'dispatch');
$controller = strpos($controller, "/") ? explode("/", $controller)[0] : $controller;
$controller = htmlentities(lcfirst($controller));

$action = $_GET['action'] ?? 'index';
$action = strpos($action, "/") ? explode("/", $action)[0] : $action;
$action = htmlentities(lcfirst($action));

$controllerNamespace = is_null($module) ? 'Controllers\\'
    : sprintf("Modules\\%s\\Controllers\\", ucfirst($module));

$controllerName = $controllerNamespace . ucfirst($controller) . 'Controller';

if (class_exists($controllerName)) {
    $requestController = new $controllerName($baseDir);
    if (!method_exists($requestController, "run")) {
        $requestController = new Controllers\PublicController($baseDir);
        $requestController->render404();
    } else {
        $requestController->run($action);
    }
} else {
    $requestController = new Controllers\PublicController($baseDir);
    $requestController->render404();
}
