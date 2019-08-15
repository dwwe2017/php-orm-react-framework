<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Helpers;

/**
 * Class ArrayHelper
 * @package Helpers
 */
class ArrayHelper
{
    /**
     * @var self|null
     */
    private static $instance;

    /**
     * @var array
     */
    private $array = [];

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
    public static function init(array $array)
    {
        if (is_null(self::$instance) || self::$instance->array !== $array) {
            self::$instance = new self($array);
        }

        return self::$instance;
    }

    /**
     * @param $array
     * @param null $key
     * @return $this
     */
    public function append($array, $key = null)
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
     * @param $name
     */
    public function __get($name)
    {
        $this->get($name);
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed|null
     */
    public function get($name, $default = null)
    {
        if (key_exists($name, $this->array)) {
            return $this->array[$name];
        }

        return $default;
    }

    /**
     * @return array
     */
    public function getArray(): array
    {
        return $this->array;
    }
}