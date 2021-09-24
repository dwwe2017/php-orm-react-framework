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

namespace Handlers;


use Configula\ConfigValues;
use Exception;
use Exceptions\MinifyJsException;
use Helpers\DirHelper;
use Helpers\FileHelper;
use JShrink\Minifier;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

class MinifyJsHandler extends Minifier
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var string
     */
    private static string $md5checksum = "";

    /**
     * @var string
     */
    private $baseDir;

    /**
     * @var array
     */
    private $defaultJsPaths;

    /**
     * @var string
     */
    private string $defaultMinifyJsDir;

    /**
     * @var string
     */
    private string $defaultMinifyJsFile = "";

    /**
     * @var array
     */
    private array $jsContent = [];

    /**
     * MinifyJsHandler constructor.
     * @param ConfigValues $config
     */
    private final function __construct(ConfigValues $config)
    {
        $this->baseDir = $config->get("base_dir");
        $this->defaultJsPaths = $config->get("default_js", []);
        $this->defaultMinifyJsDir = sprintf("%s/data/cache/js", $this->baseDir);

        FileHelper::init($this->defaultMinifyJsDir, MinifyJsException::class)
            ->isWritable(true);

        /**
         * Check and create directory restriction
         */
        DirHelper::init($this->defaultMinifyJsDir)->addDirectoryRestriction(["js"]);
    }

    /**
     *
     */
    private function setDefaults()
    {
        if(empty($this->defaultJsPaths)){
            return;
        }

        foreach ($this->defaultJsPaths as $jsPath) {
            $this->addJsContent($jsPath);
        }
    }

    /**
     * @param ConfigValues $config
     * @return MinifyJsHandler|null
     */
    public static final function init(ConfigValues $config): ?MinifyJsHandler
    {
        if (is_null(self::$instance) || serialize($config) !== self::$instanceKey) {
            self::$instance = new self($config);
            self::$instanceKey = serialize($config);
        }

        self::$instance->setDefaults();
        return self::$instance;
    }

    /**
     * @param bool $clearOldFiles
     * @return bool|int
     * @throws MinifyJsException
     */
    public final function compile(bool $clearOldFiles = true)
    {
        if(empty($this->jsContent)){
            return false;
        }

        $this->defaultMinifyJsFile = sprintf("%s/%s.js", $this->defaultMinifyJsDir, md5(self::$md5checksum));

        if ($clearOldFiles) {
            $oldDate = time() - 3600;
            $cachedFiles = scandir($this->defaultMinifyJsDir);
            foreach ($cachedFiles as $file) {
                $filepath = sprintf("%s/%s", $this->defaultMinifyJsDir, $file);
                $fileMtime = @filemtime($filepath);
                if (strlen($file) == 35 && ($fileMtime === false || $fileMtime < $oldDate)) {
                    @unlink($filepath);
                }
            }
        }

        if (!file_exists($this->getDefaultMinifyJsFile())) {
            $content = "";
            foreach ($this->jsContent as $item) {
                $content .= strlen($item) < 999 && is_file($item) ? file_get_contents($item) : trim($item);
            }

            try {
                return @file_put_contents($this->getDefaultMinifyJsFile(), self::minify($content));
            } catch (Exception $e) {
                throw new MinifyJsException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return true;
    }

    /**
     * @param bool $relative
     * @return string|null
     */
    public final function getDefaultMinifyJsFile(bool $relative = false): ?string
    {
        return $relative ? substr(str_replace($this->baseDir, "", $this->defaultMinifyJsFile), 1) : $this->defaultMinifyJsFile;
    }

    /**
     * @param string|null $fileOrString
     * @param bool $codeAsString
     */
    public final function addJsContent(?string $fileOrString, bool $codeAsString = false): void
    {
        if (is_null($fileOrString)) {
            return;
        }

        if ($codeAsString || strcasecmp(substr($fileOrString, -3), ".js") != 0) {
            self::$md5checksum .= trim(md5($fileOrString));
        } elseif (strcasecmp(substr($fileOrString, 0, 4), "http") == 0) {
            $fileOrString = @file_get_contents($fileOrString);
            self::$md5checksum .= trim(md5($fileOrString));
        } else {
            FileHelper::init($fileOrString, MinifyJsException::class)->isReadable();
            $fileMtime = @filemtime($fileOrString);
            self::$md5checksum .= date('YmdHis', $fileMtime ?: NULL) . $fileOrString;
        }

        $this->jsContent[] = $fileOrString;
    }

    /**
     * @param array $jsContent
     */
    public final function setJsContent(array $jsContent): void
    {
        $this->jsContent = [];

        foreach ($jsContent as $item) {
            $this->addJsContent($item);
        }
    }
}
