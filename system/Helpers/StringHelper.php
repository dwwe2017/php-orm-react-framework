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
     * @return string
     */
    public function decamelize()
    {
        return ltrim(
            strtolower(
                preg_replace('/([A-Z])/', '_$1', $this->string)
            ),
            '_'
        );
    }

    /**
     * @return mixed
     */
    public function camelize()
    {
        return str_replace(
            ' ',
            '',
            ucwords(
                str_replace('_', ' ', $this->string)
            )
        );
    }

    /**
     * @return string
     */
    public function camelizeLcFirst()
    {
        return lcfirst(
            $this->camelize()
        );
    }
}