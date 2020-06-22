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


use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Exceptions\InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class AnnotationHelper
 * @package Helpers
 */
class AnnotationHelper
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var string
     */
    const DEFAULT_NAMESPACE = "Annotations\\";

    /**
     * @var ReflectionClass|ReflectionMethod
     */
    private $reflectionInstance;

    /**
     * @var AnnotationReader
     */
    private AnnotationReader $annotationReader;

    /**
     * @var string
     */
    private $annotationName;

    /**
     * @var bool
     */
    private bool $reflectionClass;

    /**
     * @var bool
     */
    private bool $reflectionMethod;

    /**
     * @var
     */
    private $annotationInstance;

    /**
     * @var bool
     */
    private bool $empty = false;

    /**
     * AnnotationHelper constructor.
     * @param ReflectionClass|ReflectionMethod $reflectionInstance
     * @param string $annotationName
     * @throws AnnotationException
     * @throws InvalidArgumentException
     */
    private function __construct($reflectionInstance, string $annotationName)
    {
        $this->reflectionClass = $reflectionInstance instanceof ReflectionClass;
        $this->reflectionMethod = $reflectionInstance instanceof ReflectionMethod;

        $this->reflectionInstance = $reflectionInstance;
        $this->annotationReader = new AnnotationReader();
        $this->annotationName = sprintf("%s%s", self::DEFAULT_NAMESPACE, $annotationName);

        if ($this->isReflectionClass()) {
            $this->annotationInstance = $this->annotationReader->getClassAnnotation($this->reflectionInstance, $this->annotationName);
        } elseif ($this->isReflectionMethod()) {
            $this->annotationInstance = $this->annotationReader->getMethodAnnotation($this->reflectionInstance, $this->annotationName);
        } else {
            throw new InvalidArgumentException("For initialization, an instance type ReflectionClass or ReflectionMethod must be passed.");
        }

        $this->getAnnotationInstance() || $this->empty = true;
    }

    /**
     * @param ReflectionClass|ReflectionMethod $reflectionInstance
     * @param string $annotationName
     * @return AnnotationHelper|null
     * @throws AnnotationException
     * @throws InvalidArgumentException
     */
    public static final function init($reflectionInstance, string $annotationName)
    {
        if (is_null(self::$instance) || serialize($reflectionInstance->getName() . $annotationName) !== self::$instanceKey) {
            self::$instance = new self($reflectionInstance, $annotationName);
            self::$instanceKey = serialize($reflectionInstance->getName() . $annotationName);
        }

        return self::$instance;
    }

    /**
     * @return bool
     */
    public function isReflectionClass(): bool
    {
        return $this->reflectionClass;
    }

    /**
     * @return bool
     */
    public function isReflectionMethod(): bool
    {
        return $this->reflectionMethod;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value): void
    {
        $this->set($name, $value);
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [];

        if($this->isEmpty()){
            return $result;
        }

        foreach ($this->getAnnotationInstance() as $key => $item){
            $result[$key] = $item;
        }

        return $result;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name): bool
    {
        return !is_null($this->get($name, false));
    }

    /**
     * @param $name
     * @param $value
     */
    public function set($name, $value): void
    {
        $this->annotationInstance->{$name} = $value;
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed|null
     */
    public function get($name, $default = null)
    {
        return $this->annotationInstance->{$name} ?? $default;
    }

    /**
     * @return mixed
     */
    public function getAnnotationInstance()
    {
        return $this->annotationInstance;
    }

    /**
     * @return string
     */
    public function getAnnotationName(): string
    {
        return $this->annotationName;
    }

    /**
     * @return AnnotationReader
     */
    private function getAnnotationReader(): AnnotationReader
    {
        return $this->annotationReader;
    }

    /**
     * @return ReflectionClass|ReflectionMethod
     */
    private function getReflectionInstance()
    {
        return $this->reflectionInstance;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->empty;
    }
}