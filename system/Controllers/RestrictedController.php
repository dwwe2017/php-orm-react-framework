<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Controllers;


use Doctrine\Common\Annotations\AnnotationException;
use Entities\Group;
use Exceptions\CacheException;
use Exceptions\DoctrineException;
use Exceptions\InvalidArgumentException;
use Exceptions\MinifyCssException;
use Exceptions\MinifyJsException;
use Handlers\NavigationHandler;
use Helpers\AnnotationHelper;
use ReflectionException;
use Traits\ControllerTraits\RestrictedControllerTrait;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class RestrictedController
 * @package Controllers
 */
class RestrictedController extends AbstractBase
{
    use RestrictedControllerTrait;

    /**
     * RestrictedController constructor.
     * @param string $baseDir
     * @throws AnnotationException
     * @throws CacheException
     * @throws DoctrineException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function __construct(string $baseDir)
    {
        parent::__construct($baseDir);

        $this->registered = $this->getSessionHandler()->isRegistered();

        if (!$this->registered) {
            $this->render403(true);
        } else {
            $this->setNavigationRoute(NavigationHandler::RESTRICTED_NAV);
        }
    }

    /**
     * @param string $action
     * @throws AnnotationException
     * @throws InvalidArgumentException
     * @throws LoaderError
     * @throws ReflectionException
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws MinifyCssException
     * @throws MinifyJsException
     */
    public function betRun(string $action): void
    {
        /**
         * Access requirement at least user
         */
        $selfReflection = $this->getReflectionHelper();
        $classAccess = AnnotationHelper::init($selfReflection, "Access");
        $classAccessLevel = $classAccess->get("role", Group::ROLE_USER);
        $classAccessLevel = $classAccessLevel >= Group::ROLE_USER ? $classAccessLevel : Group::ROLE_USER;
        if (!$this->getSessionHandler()->hasRequiredRole($classAccessLevel)) {
            $this->render403();
        }

        $methodName = sprintf("%sAction", $action);
        $methodAccess = AnnotationHelper::init($selfReflection->getMethod($methodName), "Access");
        $methodAccessLevel = $methodAccess->get("role", $classAccessLevel);
        $methodAccessLevel = $methodAccessLevel >= $classAccessLevel ? $methodAccessLevel : $classAccessLevel;
        if (!$this->getSessionHandler()->hasRequiredRole($methodAccessLevel)) {
            $this->render403();
        }

        $this->postRun($action);
    }

    /**
     *
     */
    public function indexAction()
    {
        // TODO: Implement indexAction() method.
    }
}
