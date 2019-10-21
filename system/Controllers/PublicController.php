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
    public function indexAction()
    {
        // TODO: Implement indexAction() method.
    }

    /**
     *
     */
    public final function loginOrRegisterAction(): void
    {
        if($this->getSessionHandler()->isRegistered()){
            $this->getRequestHandler()->doRedirect();
        }

        $this->setJs([
            "assets/js/libs/jquery-3.4.1.min.js",
            "assets/js/libs/bootstrap.min.js",
            "assets/js/libs/lodash.compat.min.js",
            "assets/js/plugins/uniform/jquery.uniform.min.js",
            "assets/js/plugins/validation/jquery.validate.min.js",
            "assets/js/plugins/nprogress/nprogress.js",
            "assets/js/plugins/cryptojs/aes.js",
            "assets/js/plugins/cryptojs/md5.js",
            "assets/js/PublicController/loginOrRegisterAction.js"
        ]);

        $this->addJs("$(document).ready(function(){ \"use strict\"; Login.init(); });", true);
    }

    /**
     *
     */
    public final function loginAction(): void
    {
        if($this->getSessionHandler()->isRegistered()){
            $this->getRequestHandler()->doRedirect();
        }

        $this->setJs([
            "assets/js/libs/jquery-3.4.1.min.js",
            "assets/js/libs/bootstrap.min.js",
            "assets/js/libs/lodash.compat.min.js",
            "assets/js/plugins/uniform/jquery.uniform.min.js",
            "assets/js/plugins/validation/jquery.validate.min.js",
            "assets/js/plugins/nprogress/nprogress.js",
            "assets/js/plugins/cryptojs/aes.js",
            "assets/js/plugins/cryptojs/md5.js",
            "assets/js/PublicController/loginAction.js"
        ]);

        $this->addJs("$(document).ready(function(){ \"use strict\"; Login.init(); });", true);
    }

    /**
     *
     */
    public final function registerAction(): void
    {
        if($this->getSessionHandler()->isRegistered()){
            $this->getRequestHandler()->doRedirect();
        }

        $this->setJs([
            "assets/js/libs/jquery-3.4.1.min.js",
            "assets/js/libs/bootstrap.min.js",
            "assets/js/libs/lodash.compat.min.js",
            "assets/js/plugins/uniform/jquery.uniform.min.js",
            "assets/js/plugins/validation/jquery.validate.min.js",
            "assets/js/plugins/nprogress/nprogress.js",
            "assets/js/plugins/cryptojs/aes.js",
            "assets/js/plugins/cryptojs/md5.js",
            "assets/js/PublicController/registerAction.js"
        ]);

        $this->addJs("$(document).ready(function(){ \"use strict\"; Login.init(); });", true);
    }
}
