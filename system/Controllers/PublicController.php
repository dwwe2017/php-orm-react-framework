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
    public final function loginAction(): void
    {
        if($this->getSessionHandler()->isRegistered()){
            $this->getRequestHandler()->doRedirect();
        }
    }

    /**
     *
     */
    public final function registerAction(): void
    {
        if($this->getSessionHandler()->isRegistered()){
            $this->getRequestHandler()->doRedirect();
        }
    }
}
