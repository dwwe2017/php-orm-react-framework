<?php

namespace Controllers;


use Interfaces\ControllerInterfaces\PublicControllerInterface;

/**
 * Class PublicController
 * @package Controllers
 */
class PublicController extends AbstractBase implements PublicControllerInterface
{
    /**
     *
     */
    public function indexAction(): void
    {
        // TODO: Implement indexAction() method.
    }

    /**
     *
     */
    public final function loginOrRegisterAction(): void
    {
        $this->addCss("assets/css/login.css");

        $this->setJs([
            "assets/js/libs/jquery-3.4.1.min.js",
            "bootstrap/js/bootstrap.min.js",
            "assets/js/libs/lodash.compat.min.js",
            "plugins/uniform/jquery.uniform.min.js",
            "plugins/validation/jquery.validate.min.js",
            "plugins/nprogress/nprogress.js",
            "plugins/cryptojs/aes.js",
            "plugins/cryptojs/md5.js",
            "assets/js/login.js"
        ]);

        $this->addJs("$(document).ready(function(){ \"use strict\"; Login.init(); });", true);
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
            "plugins/cryptojs/aes.js",
            "plugins/cryptojs/md5.js",
            "assets/js/login.js"
        ]);

        $this->addJs("$(document).ready(function(){ \"use strict\"; Login.init(); });", true);
    }

    /**
     *
     */
    public final function registerAction(): void
    {
        $this->addCss("assets/css/login.css");

        $this->setJs([
            "assets/js/libs/jquery-3.4.1.min.js",
            "bootstrap/js/bootstrap.min.js",
            "assets/js/libs/lodash.compat.min.js",
            "plugins/uniform/jquery.uniform.min.js",
            "plugins/validation/jquery.validate.min.js",
            "plugins/nprogress/nprogress.js",
            "plugins/cryptojs/aes.js",
            "plugins/cryptojs/md5.js",
            "assets/js/login.js"
        ]);

        $this->addJs("$(document).ready(function(){ \"use strict\"; Login.init(); });", true);
    }
}
