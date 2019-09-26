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
use Exceptions\SessionException;
use Helpers\AnnotationHelper;
use Interfaces\ControllerInterfaces\RestrictedControllerInterface;
use ReflectionException;
use Traits\ControllerTraits\RestrictedControllerTrait;

/**
 * Class RestrictedController
 * @package Controllers
 */
class RestrictedController extends AbstractBase implements RestrictedControllerInterface
{
    use RestrictedControllerTrait;

    /**
     * RestrictedController constructor.
     * @param string $baseDir
     * @throws AnnotationException
     * @throws CacheException
     * @throws DoctrineException
     * @throws ReflectionException
     * @throws InvalidArgumentException
     * @throws SessionException
     */
    public function __construct(string $baseDir)
    {
        parent::__construct($baseDir);

        $this->registered = $this->getSessionHandler()->isRegistered();

        if (!$this->registered) {
            $this->render403(true);
        }
    }

    /**
     * @param string $action
     * @throws AnnotationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws \Throwable
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function run(string $action)
    {
        $methodName = sprintf("%sAction", $action);

        $selfReflection = $this->getReflectionHelper();
        $classAccess = AnnotationHelper::init($selfReflection, "Access");
        $classAccessLevel = $classAccess->get("role", Group::ROLE_USER);
        if(!$this->getSessionHandler()->hasRequiredRole($classAccessLevel)){
            $this->render403();
        }

        $methodAccess = AnnotationHelper::init($selfReflection->getMethod($methodName), "Access");
        $methodAccessLevel = $methodAccess->get("role", $classAccessLevel);
        $methodAccessLevel = $methodAccessLevel >= $classAccessLevel ? $methodAccessLevel : $classAccessLevel;
        if(!$this->getSessionHandler()->hasRequiredRole($methodAccessLevel)){
            $this->render403();
        }

        parent::run($action);
    }

    /**
     *
     */
    public function indexAction(): void
    {
        // TODO: Implement indexAction() method.
    }
}