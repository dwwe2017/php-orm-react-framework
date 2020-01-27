<?php

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

    /**
     *
     */
    public final function signOutAction(): void
    {
        $this->getSessionHandler()->signOut();
        $this->redirect(null, "publicFront", "login");
    }
}
