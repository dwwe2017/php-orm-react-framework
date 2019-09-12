<?php


namespace Handlers;


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
     * @var int
     */
    const ANY = -1;

    /**
     * @var int
     */
    const USER = 1;

    /**
     * @var int
     */
    const RESELLER = 2;

    /**
     * @var int
     */
    const ADMIN = 3;

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
        $this->setNavType($controllerInstance);
        $baseDir = $controllerInstance->getBaseDir();
        $modulesBaseDir = sprintf("%s/modules", $baseDir);
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
        $this->routes["sidebar"] = [];
        $this->routes["top_menu"] = [];
        $annotationReader = new AnnotationReader();

        foreach ($this->modulesNamespaces as $key => $modulesNamespace) {
            if (is_array($modulesNamespace)) {
                $key = strtolower($key);
                foreach ($modulesNamespace as $item) {

                    $reflectionClass = new ReflectionClass($item);
                    $reflectionClassName = $reflectionClass->getName();
                    $reflectionClassNamespace = $reflectionClass->getNamespaceName();
                    $reflectionClassShortName = $reflectionClass->getShortName();

                    $active = get_class($controllerInstance) === $reflectionClassName;
                    $accessAnnotationsParent = $annotationReader->getClassAnnotation($reflectionClass, "Annotations\\Access");
                    $accessType = $this->getNavTypeFromReflection($reflectionClass);
                    $accessRoleParent = $accessAnnotationsParent->role ? constant(strtoupper("self::" . $accessAnnotationsParent->role))
                        : ($accessType === self::PUBLIC_NAV ? $this::ANY : $this::USER);

                    $infoAnnotations = $annotationReader->getClassAnnotation($reflectionClass, "Annotations\\Info");

                    $reflectionMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
                    foreach ($reflectionMethods as $method) {
                        if (!StringHelper::init($method)->hasFilter("Action")) {
                            continue;
                        }

                        $moduleShortName = StringHelper::init($reflectionClassNamespace)->replace(["Modules\\", "\\Controllers"], "")->getString();
                        $controllerShortName = StringHelper::init($reflectionClassShortName)->replace("Controller", "")->getString();
                        $actionShortName = ucfirst(StringHelper::init($method->getName())->replace("Action", "")->getString());

                        $navKeys = [
                            "sidebar" => $annotationReader->getMethodAnnotation($method, "Annotations\\Sidebar"),
                            "top_menu" => $annotationReader->getMethodAnnotation($method, "Annotations\\TopMenu"),
                            "misc" => ""
                        ];

                        foreach ($navKeys as $navKey => $options) {
                            if (!$options) {
                                continue;
                            }

                            $this->routes[$navKey][$key]["active"] = $active;
                            $this->routes[$navKey][$key]["access_type"] = $accessType;
                            $this->routes[$navKey][$key]["access_role"] = $this->getRoleReadable($accessRoleParent);
                            $this->routes[$navKey][$key]["info"] = $infoAnnotations;

                            $accessAnnotationsChild[$navKey] = $annotationReader->getMethodAnnotation($method, "Annotations\\Access");
                            $accessRoleChild[$navKey] = $this->getAtLeastParentRole($accessRoleParent, $accessAnnotationsChild[$navKey]);
                            $infoAnnotations = $annotationReader->getMethodAnnotation($method, "Annotations\\Info");

                            $this->routes[$navKey][$key]["routes"][] = [
                                "access_role" => $this->getRoleReadable($accessRoleChild[$navKey]),
                                "active" => $this->currentAction === lcfirst($actionShortName),
                                "options" => $options,
                                "info" => $infoAnnotations,
                                "module" => lcfirst($moduleShortName),
                                "controller" => lcfirst($controllerShortName),
                                "action" => lcfirst($actionShortName)
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
    public function getRoleReadable($accessRoleConstant)
    {
        switch ($accessRoleConstant){
            case self::ADMIN:
                return "admin";
            case self::RESELLER:
                return "reseller";
            case self::USER:
                return "user";
            case self::ANY:
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
        $parent = $parent ?? self::ANY;
        $child = $child->role ?? "ANY";
        $child = constant("self::" . strtoupper($child));

        return $child<$parent ? $parent : $child;
    }
}