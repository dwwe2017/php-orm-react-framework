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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Helpers;


use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class ReflectionHelper
 * @package Helpers
 */
class ReflectionHelper
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var object
     */
    private object $object;

    /**
     * @var array
     */
    private array $attributes = [];

    /**
     * @var array
     */
    private array $methods = [];

    /**
     * ReflectionHelper constructor.
     * @param object $object
     * @param bool $onlyProperties
     */
    private function __construct(object $object, bool $onlyProperties = true)
    {
        $this->object = $object;
        $this->setAttributes($onlyProperties);
        $this->setMethods();
    }

    /**
     * @param object $object
     * @param bool $onlyProperties
     * @return ReflectionHelper|null
     */
    public static final function init(object $object, bool $onlyProperties = true): ?ReflectionHelper
    {
        if (is_null(self::$instance) || serialize($object).serialize($onlyProperties) !== self::$instanceKey) {
            self::$instance = new self($object, $onlyProperties);
            self::$instanceKey = serialize($object).serialize($onlyProperties);
        }

        return self::$instance;
    }

    /**
     * @param bool $onlyProperties
     */
    public final function setAttributes(bool $onlyProperties = true): void
    {
        $className = get_class($this->object);
        foreach ((array)$this->object as $name => $value) {
            $name = explode("\0", (string)$name);
            if (count($name) === 1) {
                $name = $name[0];
            } else {
                if ($name[1] !== $className) {
                    $name = $onlyProperties ? $name[2] : $name[1] . '::' . $name[2];
                } else {
                    $name = $name[2];
                }
            }
            $this->attributes[$name] = $value;
        }
    }

    /**
     * @return array
     */
    public final function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     *
     */
    public final function setMethods(): void
    {
        $this->methods = get_class_methods($this->object);
    }

    /**
     * @return array
     */
    public final function getMethods(): array
    {
        return $this->methods;
    }
}