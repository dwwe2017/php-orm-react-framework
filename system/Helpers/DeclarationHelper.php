<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Helpers;

use Exception;

/**
 * Class DeclarationHelper
 * @package Helpers
 */
class DeclarationHelper
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
     * @var string|null
     */
    private $extension;

    /**
     * @var string|null
     */
    private $class;

    /**
     * @var string|null
     */
    private $function;

    /**
     * @var string|null
     */
    private $exceptionClass = null;

    /**
     * DeclarationHelper constructor.
     * @param string|null $extension
     * @param string|null $class
     * @param string|null $function
     * @param string|null $exceptionClass
     */
    private function __construct(?string $extension = null, ?string $class = null, ?string $function = null, ?string $exceptionClass = null)
    {
        $this->extension = $extension;
        $this->class = $class;
        $this->function = $function;
        $this->exceptionClass = $exceptionClass;
    }

    /**
     * @param string|null $extension
     * @param string|null $class
     * @param string|null $function
     * @param string|null $exceptionClass
     * @return DeclarationHelper|null
     */
    public static function init(?string $extension = null, ?string $class = null, ?string $function = null, ?string $exceptionClass = null)
    {
        if (is_null(self::$instance) || serialize($extension.$class.$function.$exceptionClass) !== self::$instanceKey) {
            self::$instance = new self($extension, $class, $function, $exceptionClass);
            self::$instanceKey = serialize($extension.$class.$function.$exceptionClass);
        }

        return self::$instance;
    }

    /**
     * @return bool
     */
    public function extensionLoaded(): bool
    {
        return extension_loaded($this->extension) ? true
            : $this->throwOrFalse(sprintf("The required extension %s could not be loaded", $this->extension));
    }

    /**
     * @return bool
     */
    public function functionExists(): bool
    {
        return function_exists($this->function) ? true
            : $this->throwOrFalse(sprintf("The required function %s could not be loaded", $this->function));
    }

    /**
     * @return bool
     */
    public function classExists(): bool
    {
        return class_exists($this->class) ? true
            : $this->throwOrFalse(sprintf("The required class %s could not be loaded", $this->class));
    }

    /**
     * @return bool
     */
    public function isDeclared(): bool
    {
        if(!is_null($this->extension) && !$this->extensionLoaded()){
            return $this->throwOrFalse(sprintf("The required extension %s could not be loaded", $this->extension));
        }

        if(!is_null($this->class) && !$this->classExists()){
            return $this->throwOrFalse(sprintf("The required class %s could not be loaded", $this->class));
        }

        if(!is_null($this->function) && !$this->functionExists()){
            $this->throwOrFalse(sprintf("The required function %s could not be loaded", $this->function));
        }

        return true;
    }

    /**
     * @param string $message
     * @return bool
     */
    private function throwOrFalse(string $message = "")
    {
        if(!is_null($this->exceptionClass))
        {
            throw new $this->exceptionClass($message);
        }
        else
        {
            return false;
        }
    }
}