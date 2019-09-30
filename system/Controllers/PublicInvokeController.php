<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Controllers;


use Exceptions\MethodNotFoundException;
use Interfaces\ControllerInterfaces\InvokeControllerInterface;

/**
 * Class InvokeController
 * @package Controllers
 */
class PublicInvokeController extends PublicController implements InvokeControllerInterface
{
    /**
     * @return mixed|null
     */
    public function indexAction()
    {
        return null;
    }

    /**
     * @param string $action
     * @return mixed
     * @throws MethodNotFoundException
     */
    public final function run(string $action)
    {
        $methodName = $action . 'Action';
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        } else {
            throw new MethodNotFoundException(sprintf("Method %s in class %s was not found or could not be loaded", $methodName, get_class($this)));
        }
    }
}
