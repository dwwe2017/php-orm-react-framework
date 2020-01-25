<?php


namespace Controllers;

use Interfaces\ControllerInterfaces\RestrictedControllerInterface;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class RestrictedFrontController
 * @package Controllers
 */
class RestrictedFrontController extends RestrictedController implements RestrictedControllerInterface
{
    /**
     * @param string $action
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Throwable
     */
    public final function run(string $action)
    {
        parent::run($action); // TODO: Change the autogenerated stub
    }

    /**
     *
     */
    public function indexAction(): void
    {
        parent::indexAction(); // TODO: Change the autogenerated stub
    }
}
