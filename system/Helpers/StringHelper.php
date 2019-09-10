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
 * Class StringHelper
 * @package Helpers
 */
class StringHelper
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var string
     */
    private $string = "";

    /**
     * StringHelper constructor.
     * @param string $string
     */
    private function __construct(string $string)
    {
        $this->string = $string;
    }

    /**
     * @param string $string
     * @return StringHelper|null
     */
    public static final function init(string $string)
    {
        if (is_null(self::$instance) || serialize($string) !== self::$instanceKey) {
            self::$instance = new self($string);
            self::$instanceKey = serialize($string);
        }

        return self::$instance;
    }

    /**
     * @return $this
     */
    public function decamelize()
    {
        $this->string = ltrim(
            strtolower(
                preg_replace('/([A-Z])/', '_$1', $this->string)
            ),
            '_'
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function camelize()
    {
        $this->string =  str_replace(
            ' ',
            '',
            ucwords(
                str_replace('_', ' ', $this->string)
            )
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function camelizeLcFirst()
    {
        $this->string = lcfirst(
            $this->camelize()
        );

        return  $this;
    }

    /**
     * @param $search
     * @param $replace
     * @return $this
     */
    public function replace($search, $replace)
    {
        $this->string = str_replace($search, $replace, $this->string);
        return $this;
    }

    /**
     * @return $this
     */
    public function toLower()
    {
        $this->string = strtolower($this->string);
        return $this;
    }

    /**
     * @return string
     */
    public function getString(): string
    {
        return $this->string;
    }
}