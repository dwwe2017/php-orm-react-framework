<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

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