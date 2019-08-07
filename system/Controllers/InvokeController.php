<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Controllers;


use Interfaces\ControllerInterfaces\InvokeControllerInterface;

/**
 * Class InvokeController
 * @package Controllers
 */
class InvokeController extends AbstractBase implements InvokeControllerInterface
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
     * @return bool|void
     */
    public function run(string $action)
    {
        $methodName = $action . 'Action';
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        } else {
            return false;
        }
    }
}
