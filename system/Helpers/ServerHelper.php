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