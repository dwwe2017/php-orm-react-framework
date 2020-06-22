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

/**
 * Class ArrayHelper
 * @package Helpers
 */
class ArrayHelper
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var array
     */
    private array $array = [];

    /**
     * ArrayHelper constructor.
     * @param array $array
     */
    private function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * @param array $array
     * @return ArrayHelper|null
     */
    public static final function init(array $array)
    {
        if (is_null(self::$instance) || serialize($array) !== self::$instanceKey) {
            self::$instance = new self($array);
            self::$instanceKey = serialize($array);
        }

        return self::$instance;
    }

    /**
     * @param $array
     * @param null $key
     * @return $this
     */
    public final function append($array, $key = null)
    {
        is_null($key)
            ? $this->array += $array
            : $this->array[$key] += $array;

        foreach ($array as $key => $value) {
            if (key_exists($key, $this->array) && is_array($value)) {
                self::append($value, $key);
            }
        }

        return $this;
    }

    /**
     * @param $class
     * @param bool $camelize
     */
    public final function mapClass($class, $camelize = true)
    {
        foreach ($this->array as $key => $value) {
            if ($camelize) {
                $setterName = 'set' . StringHelper::init($key)->camelize()->getString();
            } else {
                $setterName = 'set' . ucfirst($key);
            }

            if (method_exists($class, $setterName)) {
                $class->$setterName($value);
            }
        }
    }

    /**
     * @param $name
     */
    public final function __get($name)
    {
        $this->get($name);
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed|null
     */
    public final function get($name, $default = null)
    {
        if (key_exists($name, $this->array)) {
            return $this->array[$name];
        }

        return $default;
    }

    /**
     * @return array
     */
    public final function getArray(): array
    {
        return $this->array;
    }
}