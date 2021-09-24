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

namespace Helpers;


use Exceptions\FileFactoryException;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class AbsolutePathHelper
 * @package Helpers
 */
class AbsolutePathHelper
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var string
     */
    private string $baseDir;

    /**
     * AbsolutePathHelper constructor.
     * @param string $baseDir
     */
    public final function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;
    }

    /**
     * @param string $baseDir
     * @return AbsolutePathHelper|null
     */
    public static final function init(string $baseDir): ?AbsolutePathHelper
    {
        if (is_null(self::$instance) || serialize($baseDir) !== self::$instanceKey) {
            self::$instance = new self($baseDir);
            self::$instanceKey = serialize($baseDir);
        }

        return self::$instance;
    }

    /**
     * @param string $relativePath
     * @param bool $throw_e
     * @return string
     */
    public final function get(string $relativePath, bool $throw_e = true): string
    {
        $absolutePath = sprintf("%s/%s", $this->getBaseDir(), $relativePath);
        FileHelper::init($absolutePath, $throw_e ? FileFactoryException::class : null)->isReadable();

        return sprintf("%s/%s", $this->getBaseDir(), $relativePath);
    }

    /**
     * @return string
     */
    public final function getBaseDir(): string
    {
        return $this->baseDir;
    }
}