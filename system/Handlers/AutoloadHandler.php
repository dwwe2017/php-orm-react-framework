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

namespace Handlers;


use Composer\Autoload\ClassLoader;
use Exceptions\FileFactoryException;
use Helpers\DirHelper;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class AutoloadHandler
 * @package Handlers
 */
class AutoloadHandler
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var ClassLoader
     */
    private ClassLoader $classLoader;

    /**
     * AutoloadHandler constructor.
     * @param string $baseDir
     * @param ClassLoader $classLoader
     */
    public final function __construct(string $baseDir, ClassLoader $classLoader)
    {
        $this->classLoader = $classLoader;

        /**
         * @internal Make annotations also available for modules
         */
        $annotations = sprintf("%s/system/Annotations", $baseDir);
        foreach (DirHelper::init($annotations, FileFactoryException::class)->getScan([".php"]) as $class) {
            $className = sprintf("Annotations\\%s", substr($class, 0, -4));
            $this->classLoader->loadClass($className);
        }

        /**
         * @internal Existing modules will be loaded automatically
         */
        $modulesDir = sprintf("%s/modules", $baseDir);
        foreach (scandir($modulesDir) as $item) {
            $path = sprintf("%s/%s", $modulesDir, $item);
            if ($item == "." || $item == ".." || is_file($path)) {
                continue;
            }

            $namespace = sprintf("Modules\\%s\\", ucfirst($item));
            $classpath = sprintf("%s/src", $path);

            $this->classLoader->addPsr4($namespace, $classpath);
        }
    }

    /**
     * @param string $baseDir
     * @param ClassLoader $classLoader
     * @return AutoloadHandler|null
     */
    public static final function init(string $baseDir, ClassLoader $classLoader)
    {
        if (is_null(self::$instance) || serialize($baseDir) . serialize($classLoader) !== self::$instanceKey) {
            self::$instance = new self($baseDir, $classLoader);
            self::$instanceKey = serialize($baseDir) . serialize($classLoader);
        }

        return self::$instance;
    }

    /**
     * @param bool $prepend
     */
    public function register($prepend = false): void
    {
        $this->classLoader->register($prepend);
    }
}