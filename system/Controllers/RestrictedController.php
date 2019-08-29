<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Controllers;


use Interfaces\ControllerInterfaces\RestrictedControllerInterface;

/**
 * Class RestrictedController
 * @package Controllers
 */
class RestrictedController extends AbstractBase implements RestrictedControllerInterface
{
    public function indexAction(): void
    {
        // TODO: Implement indexAction() method.
    }

    /**
     *
     */
    public final function loginAction(): void
    {
        $this->addCss("assets/css/login.css");
        $this->setJs([
            "assets/js/libs/jquery-3.4.1.min.js",
            "bootstrap/js/bootstrap.min.js",
            "assets/js/libs/lodash.compat.min.js",
            "plugins/uniform/jquery.uniform.min.js",
            "plugins/validation/jquery.validate.min.js",
            "plugins/nprogress/nprogress.js",
            "assets/js/login.js"
        ]);

        $this->addJs("$(document).ready(function(){ \"use strict\"; Login.init(); });", true);
    }
}