<?php


namespace Handlers;


use Annotations\Access;
use Controllers\AbstractBase;
use Controllers\PublicController;
use Controllers\SettingsController;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Exceptions\NavigationException;
use Helpers\DirHelper;
use Helpers\StringHelper;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

class NavigationHandler
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var string
     */
    const RESTRICTED_NAV = "restricted";

    /**
     * @var string
     */
    const PUBLIC_NAV = "public";

    /**
     * @var string
     */
    const SETTINGS_NAV = "settings";

    /**
     * @var string
     */
    private $modulesBaseDir = "";

    /**
     * @var array
     */
    private $modulesNamespaces = [];

    /**
     * @var array
     */
    private $modulesClassPaths = [];

    /**
     * @var string
     */
    private $navType = self::RESTRICTED_NAV;

    /**
     * @var string
     */
    private $currentAction = "index";

    /**
     * @var array
     */
    private $routes = [];

    /**
     * @var array
     */
    private $navigation = [];

    /**
     * NavigationHandler constructor.
     * @param AbstractBase $controllerInstance
     * @throws AnnotationException
     * @throws ReflectionException
     */
    private final function __construct(AbstractBase $controllerInstance)
    {
        $baseDir = $controllerInstance->getBaseDir();
        $modulesBaseDir = sprintf("%s/modules", $baseDir);
        $this->setNavType($controllerInstance);
        $haystack = DirHelper::init($modulesBaseDir,
            NavigationException::class)->getScan();

        foreach ($haystack as $item) {
            $itemPath = sprintf("%s/%s", $modulesBaseDir, $item);
            $modulePath = DirHelper::init(sprintf("%s/src/Controllers", $itemPath))->getScan();
            foreach ($modulePath as $value) {
                $controllerShortName = StringHelper::init($value)->replace(".php", "")->getString();
                $this->modulesNamespaces[$item][] = sprintf("\\Modules\\%s\\Controllers\\%s", ucfirst($item), $controllerShortName);
                $this->modulesClassPaths[$item][] = sprintf("%s/src/Controllers", $itemPath);
            }
        }

        $this->currentAction = htmlentities($_GET["action"] ?? "index");
        $this->setRoutes($controllerInstance);
    }

    /**
     * @param AbstractBase $controllerInstance
     * @return NavigationHandler|null
     * @throws AnnotationException
     * @throws ReflectionException
     */
    public static function init(AbstractBase $controllerInstance)
    {
        if (is_null(self::$instance) || serialize($controllerInstance) !== self::$instanceKey) {
            self::$instance = new self($controllerInstance);
            self::$instanceKey = serialize($controllerInstance);
        }

        return self::$instance;
    }

    /**
     * @param AbstractBase $controllerInstance
     */
    public function setNavType(AbstractBase $controllerInstance): void
    {
        if ($controllerInstance instanceof PublicController) {
            $this->navType = self::PUBLIC_NAV;
        } elseif ($controllerInstance instanceof SettingsController) {
            $this->navType = self::SETTINGS_NAV;
        } else {
            $this->navType = self::RESTRICTED_NAV;
        }
    }

    /**
     * @param ReflectionClass $class
     * @return string
     */
    private function getNavTypeFromReflection(ReflectionClass $class)
    {
        $parentClassName = strtolower($class->getParentClass()->getShortName());
        if (StringHelper::init($parentClassName)->hasFilter(self::PUBLIC_NAV)) {
            return self::PUBLIC_NAV;
        } elseif (StringHelper::init($parentClassName)->hasFilter(self::SETTINGS_NAV)) {
            return self::SETTINGS_NAV;
        }

        return self::RESTRICTED_NAV;
    }

    /**
     * @param AbstractBase $controllerInstance
     * @throws ReflectionException
     * @throws AnnotationException
     */
    private function setRoutes(AbstractBase $controllerInstance): void
    {
        $annotationReader = new AnnotationReader();

        foreach ($this->modulesNamespaces as $key => $modulesNamespace) {
            if (is_array($modulesNamespace)) {

                $key = strtolower($key);

                foreach ($modulesNamespace as $item) {

                    $reflectionClass = new ReflectionClass($item);
                    $reflectionClassNavigationAnnotation = $annotationReader
                        ->getClassAnnotation($reflectionClass, "Annotations\\Navigation");

                    if (!$reflectionClassNavigationAnnotation || !$reflectionClassNavigationAnnotation->position) {
                        continue;
                    }

                    if(!$reflectionClassNavigationAnnotation->text){
                        $reflectionClassNavigationAnnotation->text = ucfirst($key);
                    }

                    $reflectionClassPosition = $reflectionClassNavigationAnnotation->position;
                    $positions = is_array($reflectionClassPosition) ? $reflectionClassPosition : [$reflectionClassPosition];

                    foreach ($positions as $position) {
                        $reflectionClassName = $reflectionClass->getName();
                        $reflectionClassNamespace = $reflectionClass->getNamespaceName();
                        $reflectionClassShortName = $reflectionClass->getShortName();

                        /**
                         * Get access level properties from annotations
                         */
                        $reflectionClassAccessAnnotation = $annotationReader->getClassAnnotation($reflectionClass, "Annotations\\Access");
                        $reflectionClassSiteAccessLevel = $this->getNavTypeFromReflection($reflectionClass);
                        $reflectionClassAccessRole = $reflectionClassAccessAnnotation->role ?? ($reflectionClassSiteAccessLevel === self::PUBLIC_NAV ? "ANY" : "USER");
                        $reflectionClassAccessRole = constant("Annotations\\Access::" . strtoupper($reflectionClassAccessRole));
                        $reflectionClassAccessRole = $reflectionClassAccessRole === Access::ANY && $reflectionClassSiteAccessLevel === self::RESTRICTED_NAV
                            ? Access::USER : $reflectionClassAccessRole;

                        /**
                         * Get some informations about class from annotations
                         */
                        $reflectionClassInfoAnnotation = $annotationReader
                            ->getClassAnnotation($reflectionClass, "Annotations\\Info");

                        /**
                         * Check if controller is selected
                         */
                        $reflectionClassPropertyIsActive = get_class($controllerInstance) === $reflectionClassName;

                        $this->routes[$position][$key]["controller_access"] = $reflectionClassSiteAccessLevel;
                        $this->routes[$position][$key]["required_user_group"] = $this->getRoleConvertedIntoReadableTerms($reflectionClassAccessRole);
                        $this->routes[$position][$key]["active"] = $reflectionClassPropertyIsActive;
                        $this->routes[$position][$key]["options"] = $this->annotationToArray($reflectionClassNavigationAnnotation);
                        $this->routes[$position][$key]["info"] = $reflectionClassInfoAnnotation;

                        $reflectionClassMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
                        foreach ($reflectionClassMethods as $method) {
                            if (!StringHelper::init($method)->hasFilter("Action")) {
                                continue;
                            }

                            $reflectionMethodSubNavigationAnnotation = $annotationReader
                                ->getMethodAnnotation($method, "Annotations\\SubNavigation");

                            if (!$reflectionMethodSubNavigationAnnotation) {
                                continue;
                            }

                            $moduleShortNameFromMethod = StringHelper::init($reflectionClassNamespace)
                                ->replace(["Modules\\", "\\Controllers"], "")->lcFirst()->getString();

                            $controllerShortNameFromMethod = StringHelper::init($reflectionClassShortName)
                                ->replace("Controller", "")->lcFirst()->getString();

                            $actionShortNameFromMethod = StringHelper::init($method->getName())
                                ->replace("Action", "")->lcFirst()->getString();

                            if(!$reflectionMethodSubNavigationAnnotation->text){
                                $reflectionMethodSubNavigationAnnotation->text = ucfirst($actionShortNameFromMethod);
                            }

                            if(!$reflectionMethodSubNavigationAnnotation->href){
                                $reflectionMethodSubNavigationAnnotation->href = sprintf("index.php?module=%s&controller=%s&action=%s",
                                $moduleShortNameFromMethod, $controllerShortNameFromMethod, $actionShortNameFromMethod);
                            }

                            $accessAnnotationsChild = $annotationReader->getMethodAnnotation($method, "Annotations\\Access");
                            $accessRoleChild = $this->getAtLeastParentRole($reflectionClassAccessRole, $accessAnnotationsChild);
                            $reflectionMethodInfoAnnotation = $annotationReader->getMethodAnnotation($method, "Annotations\\Info");

                            $this->routes[$position][$key]["routes"][] = [
                                "required_user_group" => $this->getRoleConvertedIntoReadableTerms($accessRoleChild),
                                "active" => $this->currentAction === lcfirst($actionShortNameFromMethod),
                                "options" => $this->annotationToArray($reflectionMethodSubNavigationAnnotation),
                                "info" => $reflectionMethodInfoAnnotation,
                                "module" => $moduleShortNameFromMethod,
                                "controller" => $controllerShortNameFromMethod,
                                "action" => $actionShortNameFromMethod
                            ];
                        }
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @param $accessRoleConstant
     * @return string
     */
    public function getRoleConvertedIntoReadableTerms($accessRoleConstant)
    {
        switch ($accessRoleConstant) {
            case Access::ROOT:
                return "root";
            case Access::ADMIN:
                return "admin";
            case Access::RESELLER:
                return "reseller";
            case Access::USER:
                return "user";
            case Access::ANY:
                return "any";
        }

        return $accessRoleConstant;
    }

    /**
     * @param $parent
     * @param $child
     * @return int
     */
    public function getAtLeastParentRole(int $parent, $child)
    {
        $parent = $parent ?? Access::ANY;
        $child = $child->role ?? "ANY";
        $child = constant("Annotations\\Access::" . strtoupper($child));

        return $child < $parent ? $parent : $child;
    }

    /**
     * @param $annotation
     * @return array
     */
    public function annotationToArray($annotation)
    {
        $result = [];
        foreach ($annotation as $key => $item){
            $result[$key] = $item;
        }

        return $result;
    }
}