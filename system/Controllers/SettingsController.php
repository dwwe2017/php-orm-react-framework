<?php

namespace Controllers;


use Doctrine\Common\Annotations\AnnotationException;
use Entities\Group;
use Exceptions\CacheException;
use Exceptions\DoctrineException;
use Exceptions\InvalidArgumentException;
use Exceptions\SessionException;
use Handlers\NavigationHandler;
use Helpers\AnnotationHelper;
use Interfaces\ControllerInterfaces\SettingsControllerInterface;
use ReflectionException;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class SettingsController
 * @package Controllers
 */
class SettingsController extends RestrictedController implements SettingsControllerInterface
{
    /**
     * SettingsController constructor.
     * @param string $baseDir
     * @throws AnnotationException
     * @throws CacheException
     * @throws DoctrineException
     * @throws InvalidArgumentException
     * @throws SessionException
     * @throws ReflectionException
     */
    public function __construct(string $baseDir)
    {
        parent::__construct($baseDir);

        $this->setNavigationRoute(NavigationHandler::SETTINGS_NAV);
    }

    /**
     * @param string $action
     * @throws AnnotationException
     * @throws InvalidArgumentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws ReflectionException
     * @throws Throwable
     */
    public final function run(string $action)
    {
        /**
         * Access requirement at least admin
         */
        $selfReflection = $this->getReflectionHelper();
        $classAccess = AnnotationHelper::init($selfReflection, "Access");
        $classAccessLevel = $classAccess->get("role", Group::ROLE_ADMIN);
        $classAccessLevel = $classAccessLevel >= Group::ROLE_ADMIN ? $classAccessLevel : Group::ROLE_ADMIN;
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