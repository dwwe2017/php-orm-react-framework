<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

declare(strict_types=1);

namespace Helpers;


/**
 * Class ReflectionHelper
 * @package Helpers
 */
class ReflectionHelper
{
    /**
     * @var self|null
     */
    private static $instance = null;

    /**
     * @var string
     */
    private static $instanceKey = "";

    /**
     * @var object
     */
    private $object;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var array
     */
    private $methods = [];

    /**
     * ReflectionHelper constructor.
     * @param object $object
     * @param bool $onlyProperties
     */
    private function __construct(object $object, $onlyProperties = true)
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
    public static function init(object $object, $onlyProperties = true)
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
    public function setAttributes($onlyProperties = true): void
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
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     *
     */
    public function setMethods(): void
    {
        $this->methods = get_class_methods($this->object);
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
}