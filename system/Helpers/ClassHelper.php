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