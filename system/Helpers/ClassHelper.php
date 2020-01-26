<?php


namespace Helpers;


use Traits\UtilTraits\InstantiationStaticsUtilTrait;
use ReflectionClass;

/**
 * Class ClassHelper
 * @package Helpers
 */
class ClassHelper
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var ReflectionClass
     */
    private ReflectionClass $reflectionClass;

    /**
     * @var string
     */
    private ?string $exceptionClass;

    /**
     * ClassHelper constructor.
     * @param ReflectionClass $class
     * @param string|null $exceptionClass
     */
    private function __construct(ReflectionClass $class, ?string $exceptionClass = null)
    {
        $this->reflectionClass = $class;
        $this->exceptionClass = $exceptionClass;
    }

    /**
     * @param ReflectionClass $class
     * @param string|null $exceptionClass
     * @return ClassHelper|null
     */
    public static final function init(ReflectionClass $class, ?string $exceptionClass = null)
    {
        if (is_null(self::$instance) || serialize($class . $exceptionClass) !== self::$instanceKey) {
            self::$instance = new self($class, $exceptionClass);
            self::$instanceKey = serialize($class . $exceptionClass);
        }

        return self::$instance;
    }

    /**
     * @param string $interfaceClass
     * @return bool
     */
    public final function hasInterface(string $interfaceClass)
    {
        $result = $this->reflectionClass->implementsInterface($interfaceClass);

        if(!$result && !is_null($this->exceptionClass) && class_exists($this->exceptionClass)){
            throw new $this->exceptionClass(sprintf("The class %s must implement the interface %s", $this->reflectionClass->getShortName(), $interfaceClass));
        }

        return $result;
    }

    /**
     * @param string $traitClass
     * @return bool
     */
    public final function hasTrait(string $traitClass)
    {
        $result = in_array($traitClass, $this->reflectionClass->getTraitNames());

        if(!$result && !is_null($this->exceptionClass) && class_exists($this->exceptionClass)){
            throw new $this->exceptionClass(sprintf("The class %s must implement the trait %s", $this->reflectionClass->getShortName(), $traitClass));
        }

        return $result;
    }

    /**
     * @return bool
     */
    public final function isModule()
    {
        return strcasecmp(substr($this->reflectionClass->getName(), 0, 8), "Modules\\") === 0;
    }

    /**
     * @return string|null
     */
    public final function getControllerShortName(): ?string
    {
        return preg_replace('/^([A-Za-z]+\\\)+/', '', $this->reflectionClass->getName()); // i.e. PublicController
    }

    /**
     * @return string|null
     */
    public final function getModuleShortName(): ?string
    {
        $nameParts = $this->isModule() ? explode("\\", $this->reflectionClass->getName()) : null;  // i.e. Dashboard
        return $this->isModule() ? $nameParts[1] : null;
    }
}