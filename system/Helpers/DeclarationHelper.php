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
     * DeclarationHelper constructor.
     * @param string|null $extension
     * @param string|null $class
     * @param string|null $function
     */
    private function __construct(?string $extension = null, ?string $class = null, ?string $function = null)
    {
        $this->extension = $extension;
        $this->class = $class;
        $this->function = $function;
    }

    /**
     * @param string|null $extension
     * @param string|null $class
     * @param string|null $function
     * @return DeclarationHelper|null
     */
    public static function init(?string $extension = null, ?string $class = null, ?string $function = null)
    {
        if (is_null(self::$instance) || serialize($extension.$class.$function) !== self::$instanceKey) {
            self::$instance = new self($extension, $class, $function);
            self::$instanceKey = serialize($extension.$class.$function);
        }

        return self::$instance;
    }

    /**
     * @return bool
     */
    public function extensionLoaded(): bool
    {
        return extension_loaded($this->extension);
    }

    /**
     * @return bool
     */
    public function functionExists(): bool
    {
        return function_exists($this->function);
    }

    /**
     * @return bool
     */
    public function classExists(): bool
    {
        return class_exists($this->class);
    }

    /**
     * @return bool
     */
    public function isDeclared(): bool
    {
        if(!is_null($this->extension) && !$this->extensionLoaded()){
            return false;
        }

        if(!is_null($this->class) && !$this->classExists()){
            return false;
        }

        if(!is_null($this->function) && !$this->functionExists()){
            return false;
        }

        return true;
    }
}