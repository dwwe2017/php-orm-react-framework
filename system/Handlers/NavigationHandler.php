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

namespace Handlers;


use Controllers\AbstractBase;
use Doctrine\Common\Annotations\AnnotationException;
use Entities\Group;
use Exceptions\InvalidArgumentException;
use Exceptions\NavigationException;
use Helpers\AnnotationHelper;
use Helpers\DirHelper;
use Helpers\ServerHelper;
use Helpers\StringHelper;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class NavigationHandler
 * @package Handlers
 */
class NavigationHandler
{
    use InstantiationStaticsUtilTrait;

    /**
     * @internal Routes of this type are only output at levels based on a RestrictedController
     */
    const RESTRICTED_NAV = "restrictedFront";

    /**
     * @internal Routes of this type are only output at levels based on a PublicController
     */
    const PUBLIC_NAV = "publicFront";

    /**
     * @internal Routes of this type are only output at levels based on a SettingsController
     */
    const SETTINGS_NAV = "settingsFront";

    /**
     * @internal Routes of this kind are displayed at every level
     */
    const ANY_NAV = "anyFront";

    /**
     * @var SessionHandler
     */
    private SessionHandler $sessionInstance;

    /**
     * @var string
     */
    private string $modulesBaseDir = "";

    /**
     * @var array
     */
    private array $modulesNamespaces = [];

    /**
     * @var string
     */
    private $currentAction = "index";

    /**
     * @var array
     */
    private array $routes = [];

    /**
     * @var array
     */
    private array $navigation = [];

    /**
     * @var array
     */
    private array $breadcrumb_routes = [];

    /**
     * NavigationHandler constructor.
     * @param AbstractBase $controllerInstance
     * @param SessionHandler $sessionInstance
     * @throws AnnotationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private final function __construct(AbstractBase $controllerInstance, SessionHandler $sessionInstance)
    {
        $this->initDefaultBreadcrumbRoutes();
        $this->sessionInstance = $sessionInstance;

        $baseDir = $controllerInstance->getBaseDir();
        $modulesBaseDir = sprintf("%s/modules", $baseDir);
        $haystack = DirHelper::init($modulesBaseDir,
            NavigationException::class)->getScan();

        $this->currentAction = htmlentities($_GET["action"] ?? "index");

        foreach ($haystack as $item) {
            $itemPath = sprintf("%s/%s", $modulesBaseDir, $item);
            $modulePath = DirHelper::init(sprintf("%s/src/Controllers", $itemPath))->getScan();
            foreach ($modulePath as $value) {
                $controllerShortName = StringHelper::init($value)->replace(".php", "")->getString();
                $this->modulesNamespaces[$item][] = sprintf("\\Modules\\%s\\Controllers\\%s", ucfirst($item), $controllerShortName);
            }
        }

        $this->initRoutes($controllerInstance);
        $this->initUserRoutes();
    }

    /**
     * @param AbstractBase $controllerInstance
     * @param SessionHandler $sessionInstance
     * @return NavigationHandler|null
     * @throws AnnotationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public static function init(AbstractBase $controllerInstance, SessionHandler $sessionInstance)
    {
        if (is_null(self::$instance) || serialize(get_class($controllerInstance) . get_class($sessionInstance)) !== self::$instanceKey) {
            self::$instance = new self($controllerInstance, $sessionInstance);
            self::$instanceKey = serialize(get_class($controllerInstance) . get_class($sessionInstance));
        }

        return self::$instance;
    }

    /**
     * @param ReflectionClass $class
     * @return string
     */
    private function getNavTypeFromReflection(ReflectionClass $class)
    {
        $parentClassName = lcfirst($class->getParentClass()->getShortName());
        if (StringHelper::init($parentClassName)->hasFilter(self::PUBLIC_NAV)) {
            return self::PUBLIC_NAV;
        } elseif (StringHelper::init($parentClassName)->hasFilter(self::SETTINGS_NAV)) {
            return self::SETTINGS_NAV;
        }

        return self::RESTRICTED_NAV;
    }

    /**
     * @param AbstractBase $controllerInstance
     * @throws AnnotationException
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    private function initRoutes(AbstractBase $controllerInstance): void
    {
        foreach ($this->modulesNamespaces as $key => $modulesNamespace) {
            if (is_array($modulesNamespace)) {

                $key = strtolower($key);

                foreach ($modulesNamespace as $namespace) {

                    $reflectionClass = new ReflectionClass($namespace);
                    $classNavigationAnnotation = AnnotationHelper::init($reflectionClass, "Navigation");

                    if ($classNavigationAnnotation->isEmpty() || (!$classNavigationAnnotation->get("position") && !$classNavigationAnnotation->get("positions"))) {
                        continue;
                    }

                    /**
                     * Get access level properties from annotations
                     */
                    $classSiteAccessLevel = $this->getNavTypeFromReflection($reflectionClass);
                    $classAccessAnnotation = AnnotationHelper::init($reflectionClass, "Access");

                    /**
                     * Setting the minimum access level depending on the parent class if no "@Access" annotation has been set
                     */
                    $minAccessRole = $this->getMinimalAccessRole($classSiteAccessLevel);
                    $reflectionClassAccessRole = $classAccessAnnotation->get("role", $minAccessRole);

                    /**
                     * @internal Check access !Root always has access to everything and everywhere
                     */
                    if (!$this->getSessionInstance()->hasRequiredRole($reflectionClassAccessRole)) {
                        continue;
                    }

                    if (!$classNavigationAnnotation->get("text")) {
                        $classNavigationAnnotation->set("text", ucfirst($key));
                    }

                    $reflectionClassRequiredGetParams = $classNavigationAnnotation->get("requiredGetParams", []);
                    $reflectionClassPropertyIsDisabled = false;

                    if(!empty($reflectionClassRequiredGetParams)){
                        foreach ($reflectionClassRequiredGetParams as $getParam){
                            if(!key_exists($getParam, $_GET)){
                                $reflectionClassPropertyIsDisabled = true;
                                break;
                            }
                        }
                    }

                    $reflectionClassPosition = $classNavigationAnnotation->get("position");
                    $reflectionClassPosition = $reflectionClassPosition ?? $classNavigationAnnotation->get("positions");
                    $positions = is_array($reflectionClassPosition) ? $reflectionClassPosition : [$reflectionClassPosition];

                    foreach ($positions as $position) {
                        $reflectionClassName = $reflectionClass->getName();
                        $reflectionClassNamespace = $reflectionClass->getNamespaceName();
                        $reflectionClassShortName = $reflectionClass->getShortName();

                        /**
                         * Get some informations about class from annotations
                         */
                        $classInfoAnnotation = AnnotationHelper::init($reflectionClass, "Info");

                        /**
                         * Check if controller is selected
                         */
                        $reflectionClassPropertyIsActive = get_class($controllerInstance) === $reflectionClassName;

                        $this->routes[$classSiteAccessLevel][$position][$key][$namespace] = [
                            "controller_access" => $classSiteAccessLevel,
                            "required_user_group_role_name" => $this->getRolesConvertedIntoReadableTerms($reflectionClassAccessRole),
                            "required_user_group_role_level" => $reflectionClassAccessRole,
                            "active" => $reflectionClassPropertyIsActive,
                            "disabled" => $reflectionClassPropertyIsDisabled,
                            "options" => $classNavigationAnnotation->toArray(),
                            "info" => $classInfoAnnotation->toArray()
                        ];

                        $reflectionClassMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
                        foreach ($reflectionClassMethods as $method) {
                            if (!StringHelper::init($method)->hasFilter("Action")) {
                                continue;
                            }

                            $methodSubNavigationAnnotation = AnnotationHelper::init($method, "SubNavigation");

                            if ($methodSubNavigationAnnotation->isEmpty()) {
                                continue;
                            }

                            /**
                             * @internal Check access !Root always has access to everything and everywhere
                             */
                            $accessAnnotationsChild = AnnotationHelper::init($method, "Access");
                            $accessRoleChild = $this->getAtLeastParentRole($reflectionClassAccessRole, $accessAnnotationsChild->getAnnotationInstance());
                            if (!$this->getSessionInstance()->hasRequiredRole($accessRoleChild)) {
                                continue;
                            }

                            $moduleShortNameFromMethod = StringHelper::init($reflectionClassNamespace)
                                ->replace(["Modules\\", "\\Controllers"], "")->lcFirst()->getString();

                            $controllerShortNameFromMethod = StringHelper::init($reflectionClassShortName)
                                ->replace("Controller", "")->lcFirst()->getString();

                            $actionShortNameFromMethod = StringHelper::init($method->getName())
                                ->replace("Action", "")->lcFirst()->getString();

                            if (!$methodSubNavigationAnnotation->get("text")) {
                                $methodSubNavigationAnnotation->set("text", ucfirst($actionShortNameFromMethod));
                            }

                            if (!$methodSubNavigationAnnotation->get("href")) {
                                if (strcasecmp($controllerShortNameFromMethod, "index") == 0
                                    && strcasecmp($actionShortNameFromMethod, "index") == 0) {
                                    $methodSubNavigationAnnotation->set("href", sprintf("index.php?module=%s",
                                        $moduleShortNameFromMethod));
                                } elseif (strcasecmp($actionShortNameFromMethod, "index") == 0) {
                                    $methodSubNavigationAnnotation->set("href", sprintf("index.php?module=%s&controller=%s",
                                        $moduleShortNameFromMethod, $controllerShortNameFromMethod));
                                } else {
                                    $methodSubNavigationAnnotation->set("href", sprintf("index.php?module=%s&controller=%s&action=%s",
                                        $moduleShortNameFromMethod, $controllerShortNameFromMethod, $actionShortNameFromMethod));
                                }
                            }

                            $reflectionMethodRequiredGetParams = $methodSubNavigationAnnotation->get("requiredGetParams", []);
                            $reflectionMethodPropertyIsDisabled = false;

                            if(!empty($reflectionMethodRequiredGetParams)){
                                foreach ($reflectionMethodRequiredGetParams as $getParam){
                                    if(!key_exists($getParam, $_GET)){
                                        $reflectionMethodPropertyIsDisabled = true;
                                        break;
                                    }
                                }
                            }

                            $methodInfoAnnotation = AnnotationHelper::init($method, "Info");

                            $this->addRoute($classSiteAccessLevel, $position, $key, $namespace, [
                                "required_user_group_role_name" => $this->getRolesConvertedIntoReadableTerms($accessRoleChild),
                                "required_user_group_role_level" => $accessRoleChild,
                                "active" => $this->getCurrentAction() === lcfirst($actionShortNameFromMethod),
                                "disabled" => $reflectionMethodPropertyIsDisabled,
                                "options" => $methodSubNavigationAnnotation->toArray(),
                                "info" => $methodInfoAnnotation->toArray(),
                                "module" => $moduleShortNameFromMethod,
                                "controller" => $controllerShortNameFromMethod,
                                "action" => $actionShortNameFromMethod
                            ]);
                        }
                    }
                }
            }
        }
    }

    /**
     * If the user is logged in, a corresponding menu is displayed
     */
    private function initUserRoutes(): void
    {
        if ($this->getSessionInstance()->isRegistered()) {

            $user = $this->getSessionInstance()->getUser();

            /**
             * @see templates/Controllers/coreui/generic.nav.macro.lib.twig::macro top_right
             */
            $this->routes[self::RESTRICTED_NAV]["user_account"]["avatar"] = $user->getAvatar();
        }

        $this->routes[self::ANY_NAV]["crump_bar"] = [
            [
                "options" => [
                    "text" => sprintf("%s", ServerHelper::getVersion()),
                    "title" => "OS",
                    "href" => "javascript:void(0)",
                    "icon" => "cil-3d"
                ]
            ],
            [
                "options" => [
                    "text" => sprintf("PHP %s", phpversion()),
                    "href" => "javascript:void(0)",
                    "icon" => "cil-code"
                ]
            ],
            [
                "options" => [
                    "text" => date("d.m.Y"),
                    "href" => "javascript:void(0)",
                    "icon" => "cil-calendar"
                ]
            ]
        ];
    }

    /**
     *
     */
    public final function initDefaultBreadcrumbRoutes()
    {
        if (isset($_GET["module"]) && !empty($_GET["module"])) {
            $this->breadcrumb_routes[] = [
                "current" => !isset($_GET["controller"]),
                "text" => StringHelper::init($_GET["module"])->decamelize()->ucFirst()->replace("_", " ")->getString(),
                "href" => isset($_GET["controller"]) ? sprintf("index.php?module=%s", $_GET["module"]) : "javascript:void(0)"
            ];

            if (isset($_GET["controller"]) && !empty($_GET["controller"])) {
                $this->breadcrumb_routes[] = [
                    "current" => !isset($_GET["action"]),
                    "text" => StringHelper::init($_GET["controller"])->decamelize()->ucFirst()->replace("_", " ")->getString(),
                    "href" => isset($_GET["action"]) ? sprintf("index.php?module=%s&controller=%s", $_GET["module"], $_GET["controller"]) : "javascript:void(0)"
                ];
            }
        } else {
            if (isset($_GET["controller"]) && !empty($_GET["controller"])) {
                $this->breadcrumb_routes[] = [
                    "current" => !isset($_GET["action"]),
                    "text" => StringHelper::init($_GET["controller"])->decamelize()->ucFirst()->replace("_", " ")->getString(),
                    "href" => isset($_GET["action"]) ? sprintf("index.php?controller=%s", $_GET["controller"]) : "javascript:void(0)"
                ];
            }
        }

        if (isset($_GET["action"]) && !empty($_GET["action"])) {
            $this->breadcrumb_routes[] = [
                "current" => true,
                "text" => StringHelper::init($_GET["action"])->decamelize()->ucFirst()->replace("_", " ")->getString(),
                "href" => "javascript:void(0)"
            ];
        }
    }

    /**
     * @param $classSiteAccessLevel
     * @param $position
     * @param $key
     * @param $namespace
     * @param array $navigationRoutes
     */
    public final function addRoute($classSiteAccessLevel, $position, $key, $namespace, array $navigationRoutes)
    {
        $this->routes[$classSiteAccessLevel][$position][$key][$namespace]["routes"][] = $navigationRoutes;
    }

    /**
     * @param string $classSiteAccessLevel
     * @return array
     */
    public final function getRoutes($classSiteAccessLevel = self::PUBLIC_NAV): array
    {
        $result = array();

        if (key_exists($classSiteAccessLevel, $this->routes)) {
            $result = $this->routes[$classSiteAccessLevel];
        }

        if(key_exists(self::ANY_NAV, $this->routes)){
            $result = array_merge($result, $this->routes[self::ANY_NAV]);
        }

        return $result;
    }

    /**
     * @return array
     */
    public final function getBreadcrumbRoutes(): array
    {
        return $this->breadcrumb_routes;
    }

    /**
     * @param array|int $accessRoleConstant
     * @return array|string
     */
    private final function getRolesConvertedIntoReadableTerms($accessRoleConstant)
    {
        if (is_array($accessRoleConstant)) {
            $result = [];
            foreach ($accessRoleConstant as $key => $item) {
                switch ($item) {
                    case Group::ROLE_ROOT:
                        $result[$key] = "ROOT";
                        break;
                    case Group::ROLE_ADMIN:
                        $result[$key] = "ADMIN";
                        break;
                    case Group::ROLE_RESELLER:
                        $result[$key] = "RESELLER";
                        break;
                    case Group::ROLE_USER:
                        $result[$key] = "USER";
                        break;
                    case Group::ROLE_ANY:
                        $result[$key] = "ANY";
                        break;
                }
            }

            return $result;

        } else {
            switch ($accessRoleConstant) {
                case Group::ROLE_ROOT:
                    return "ROOT";
                case Group::ROLE_ADMIN:
                    return "ADMIN";
                case Group::ROLE_RESELLER:
                    return "RESELLER";
                case Group::ROLE_USER:
                    return "USER";
                case Group::ROLE_ANY:
                    return "ANY";
            }

            return $accessRoleConstant;
        }
    }

    /**
     * @param $parent
     * @param $child
     * @return int
     */
    private function getAtLeastParentRole(int $parent, $child)
    {
        $parent = $parent ?? Group::ROLE_ANY;
        $child = $child->role ?? Group::ROLE_ANY;

        return $child < $parent ? $parent : $child;
    }

    /**
     * @param string $reflectionClassSiteAccessLevel
     * @return int
     */
    private function getMinimalAccessRole(string $reflectionClassSiteAccessLevel)
    {
        $result = Group::ROLE_ANY;

        switch ($reflectionClassSiteAccessLevel) {
            case self::RESTRICTED_NAV:
                $result = Group::ROLE_USER;
                break;
            case self::SETTINGS_NAV:
                $result = Group::ROLE_ADMIN;
                break;
        }

        return $result;
    }

    /**
     * @return SessionHandler
     */
    private function getSessionInstance(): SessionHandler
    {
        return $this->sessionInstance;
    }

    /**
     * @return string
     */
    private function getCurrentAction(): string
    {
        return $this->currentAction;
    }
}
