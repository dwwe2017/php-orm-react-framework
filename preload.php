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

use Doctrine\Persistence\ObjectManagerAware;
use Doctrine\Persistence\PropertyChangedListener;

/**
 * Class Preloader ~/~ AN EXPERIMENT!!! ~/~
 *
 * @inheritDoc
 * For preloading to work, you have to tell the server which files to load.
 * To activate preloading you have to add the following line in php.ini:
 * 'opcache.preload=/var/www/htdocs/project/preload.php'
 * We're now able to preload/load the whole framework
 * @author https://stitcher.io/blog/preloading-in-php-74
 * @todo Keep working on it...
 */
class Preloader
{
    /**
     * @var array
     */
    private array $ignores = [];

    /**
     * @var int
     */
    private static int $count = 0;

    /**
     * @var array|string[]
     */
    private array $paths;

    /**
     * @var array
     */
    private array $fileMap;

    /**
     * Preloader constructor.
     * @param string ...$paths
     */
    public function __construct(...$paths)
    {
        $this->paths = $paths;
        $classMap = require __DIR__ . '/vendor/composer/autoload_classmap.php';
        $this->fileMap = array_flip($classMap);
    }

    /**
     * @param string ...$paths
     * @return Preloader
     */
    public function paths(...$paths): Preloader
    {
        $this->paths = array_merge(
            $this->paths,
            $paths
        );

        return $this;
    }

    /**
     * @param string ...$names
     * @return Preloader
     */
    public function ignore(...$names): Preloader
    {
        $this->ignores = array_merge(
            $this->ignores,
            $names
        );

        return $this;
    }

    /**
     *
     */
    public function load(): void
    {
        foreach ($this->paths as $path) {
            $this->loadPath(rtrim($path, '/'));
        }

        $count = self::$count;

        echo "[Preloader] Preloaded {$count} classes" . PHP_EOL;
    }

    /**
     * @param string $path
     */
    private function loadPath(string $path): void
    {
        if (is_dir($path)) {
            $this->loadDir($path);

            return;
        }

        $this->loadFile($path);
    }

    /**
     * @param string $path
     */
    private function loadDir(string $path): void
    {
        $handle = opendir($path);

        while ($file = readdir($handle)) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            $this->loadPath("{$path}/{$file}");
        }

        closedir($handle);
    }

    /**
     * @param string $path
     */
    private function loadFile(string $path): void
    {
        $class = $this->fileMap[$path] ?? null;

        if ($this->shouldIgnore($class)) {
            return;
        }

        require_once($path);

        self::$count++;

        echo "[Preloader] Preloaded `{$class}`" . PHP_EOL;
    }

    /**
     * @param string|null $name
     * @return bool
     */
    private function shouldIgnore(?string $name): bool
    {
        if ($name === null) {
            return true;
        }

        foreach ($this->ignores as $ignore) {
            if (strpos($name, $ignore) === 0) {
                return true;
            }
        }

        return false;
    }
}

(new Preloader(__DIR__ . "/system/Controllers"))->load();