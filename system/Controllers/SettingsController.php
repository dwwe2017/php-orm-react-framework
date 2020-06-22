<?php
/**
 * MIT License
 *
 * Copyright (c) 2020 DW Web-Engineering
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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