<?php

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
    public static final function init(object $object, $onlyProperties = true)
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
    public final function setAttributes($onlyProperties = true): void
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