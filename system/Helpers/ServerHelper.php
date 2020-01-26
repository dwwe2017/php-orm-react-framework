<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2020. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Helpers;


/**
 * Class ServerHelper
 * @package Helpers
 */
class ServerHelper
{
    /**
     * @param string $basePath
     * @return bool
     */
    public static function isZendServer($basePath = __DIR__): bool
    {
        return strpos($basePath, "/usr/local/zend/") !== false;
    }

    /**
     * @return bool
     */
    public static function isApacheServer()
    {
        return function_exists("apache_get_version");
    }

    /**
     * @return string|null
     */
    public static function getVersion(): ?string
    {
        $result = null;
        if (self::isApacheServer()) {
            if (preg_match('|Apache/(\d+)\.(\d+)\.(\d+)|', @apache_get_version(), $version)) {
                $result = $version[1] . '.' . $version[2] . '.' . $version[3];
            }
        } elseif (self::isZendServer()) {
            $result = "Zend";
        } elseif (isset($_SERVER['SERVER_SOFTWARE'])) {
            if (preg_match('|Apache/(\d+)\.(\d+)\.(\d+)|', $_SERVER['SERVER_SOFTWARE'], $version)) {
                $result = $version[1] . '.' . $version[2] . '.' . $version[3];
            } elseif (!is_array($_SERVER['SERVER_SOFTWARE'])) {
                $result = $_SERVER['SERVER_SOFTWARE'];
            }
        }

        return $result;
    }
}