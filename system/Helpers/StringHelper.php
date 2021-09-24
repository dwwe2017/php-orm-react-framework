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
    private string $string;

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
    public static final function init(string $string): ?StringHelper
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
    public function decamelize(): StringHelper
    {
        $this->string = ltrim(strtolower(preg_replace('/([A-Z])/', '_$1', $this->string)), '_');
        return $this;
    }

    /**
     * @return $this
     */
    public function camelize(): StringHelper
    {
        $this->string = str_replace(
            ' ',
            '',
            ucwords(str_replace('_', ' ', $this->string))
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function camelizeLcFirst(): StringHelper
    {
        $this->string = lcfirst(str_replace(
            ' ',
            '',
            ucwords(str_replace('_', ' ', $this->string))
        ));

        return $this;
    }

    /**
     * @param $search
     * @param $replace
     * @return $this
     */
    public function replace($search, $replace): StringHelper
    {
        $this->string = str_replace($search, $replace, $this->string);
        return $this;
    }

    /**
     * @return $this
     */
    public function toLower(): StringHelper
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

    /**
     * @param string $filter
     * @return bool
     */
    public function hasFilter(string $filter): bool
    {
        return strpos($this->string, $filter) !== false;
    }

    /**
     * @return $this
     */
    public function lcFirst(): StringHelper
    {
        $this->string = lcfirst($this->string);
        return $this;
    }

    /**
     * @return $this
     */
    public function ucFirst(): StringHelper
    {
        $this->string = ucfirst($this->string);
        return $this;
    }

    /**
     * @return $this
     */
    public function rmNamespace(): StringHelper
    {
        $lastSeparator = strripos($this->string, "\\") + 1;
        $this->string = substr($this->string, $lastSeparator);

        return $this;
    }
}
