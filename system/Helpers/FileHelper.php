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

class FileHelper
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var string
     */
    private string $file = "";

    /**
     * @var string
     */
    private string $fileType = "file";

    /**
     * @var string|null
     */
    private ?string $exceptionClass = null;

    /**
     * FileHelper constructor.
     * @param string $file
     * @param string|null $exceptionClass
     */
    private function __construct(string $file, ?string $exceptionClass = null)
    {
        $this->file = $file;
        $this->fileType = is_dir($this->file) ? "directory" : "file";
        $this->exceptionClass = class_exists($exceptionClass) ? $exceptionClass : null;
    }

    /**
     * @param string $file
     * @param string|null $exceptionClass
     * @return FileHelper|null
     */
    public static final function init(string $file, ?string $exceptionClass = null)
    {
        if (is_null(self::$instance) || serialize($file.$exceptionClass) !== self::$instanceKey) {
            self::$instance = new self($file, $exceptionClass);
            self::$instanceKey = serialize($file.$exceptionClass);
        }

        return self::$instance;
    }

    /**
     * @param bool $mkdir
     * @return bool
     */
    public final function fileExists($mkdir = false)
    {
        if (!file_exists($this->file)) {
            if ($mkdir) {
                if (!@mkdir($this->file, 0777, true)) {
                    if (!is_null($this->exceptionClass)) {
                        throw new $this->exceptionClass(sprintf("The required %s '%s' can not be created, please check the directory permissions or create it manually.", $this->fileType, $this->file), E_ERROR);
                    }
                    return false;
                }
            } elseif (!is_null($this->exceptionClass)) {
                throw new $this->exceptionClass(sprintf("The %s '%s' could not be found", $this->fileType, $this->file), E_ERROR);
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public final function isReadable()
    {
        if (!$this->fileExists()) {
            return false;
        } elseif (!is_readable($this->file)) {
            if (!is_null($this->exceptionClass)) {
                throw new $this->exceptionClass(sprintf("The %s '%s' could not be loaded, please check the file and directory permissions", $this->fileType, $this->file), E_ERROR);
            }

            return false;
        }

        return true;
    }

    /**
     * @param bool $mkdirAndSetChmod
     * @return bool
     */
    public final function isWritable($mkdirAndSetChmod = false)
    {
        if (!$this->fileExists($mkdirAndSetChmod)) {
            return false;
        } elseif (!is_writable($this->file)) {
            if ($mkdirAndSetChmod) {
                if (!@chmod($this->file, 0777)) {
                    if (!is_null($this->exceptionClass)) {
                        throw new $this->exceptionClass(sprintf("The required %s '%s' can not be written, please check the directory permissions.", $this->fileType, $this->file), E_ERROR);
                    }
                    return false;
                }
            } elseif (!is_null($this->exceptionClass)) {
                throw new $this->exceptionClass(sprintf("The %s '%s' could not be loaded, please check the file and directory permissions", $this->fileType, $this->file), E_ERROR);
            }

            return false;
        }

        return true;
    }

    /**
     *
     */
    public final function delete(): void
    {
        if($this->fileExists())
        {
            @unlink($this->file);
        }
    }

    /**
     * @param null $default
     * @return mixed
     */
    public final function getContents($default = null)
    {
        if($this->isReadable())
        {
            $result = file_get_contents($this->file);
            return $result !== false ? $result : $default;
        }

        return $default;
    }

    /**
     * @param $data
     * @param int $flags
     * @param null $context
     * @return false|int|null
     */
    public final function putContents($data, $flags = 0, $context = null)
    {
        return @file_put_contents($this->file, $data, $flags, $context);
    }
}
