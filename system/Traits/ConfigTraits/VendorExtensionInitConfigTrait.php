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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Traits\ConfigTraits;


use Configs\DefaultConfig;
use Configula\ConfigValues;

/**
 * Trait VendorExtensionInitConfigTrait
 * @package Traits\ConfigTraits
 */
trait VendorExtensionInitConfigTrait
{
    /**
     * @var ConfigValues|null
     */
    private ?ConfigValues $configValues = null;

    /**
     * @var ConfigValues|null
     */
    private ?ConfigValues $config;

    /**
     * self constructor.
     * @param DefaultConfig $defaultConfig
     */
    public function __construct(DefaultConfig $defaultConfig)
    {

    }

    /**
     * @param DefaultConfig $defaultConfig
     * @return ConfigValues
     */
    public static function init(DefaultConfig $defaultConfig): ConfigValues
    {
        if (is_null(self::$instance) || serialize($defaultConfig) !== self::$instanceKey) {
            self::$instance = new self($defaultConfig);
            self::$instanceKey = serialize($defaultConfig);
        }

        return self::$instance->configValues;
    }
}