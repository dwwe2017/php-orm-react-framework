<?php


namespace Controllers;

/**
 * Class DispatchController
 * @package Controllers
 */
class DispatchController extends AbstractBase
{
    /**
     *
     */
    public function indexAction(): void
    {
        $this->renderEntry();
    }

    /**
     * @param string $action
     */
    public function run(string $action): void
    {
        $methodName = sprintf("%sAction", $action);
        if (method_exists($this, $methodName)) {
            $this->$methodName();
        } else {
            $this->render404();
        }
    }
}