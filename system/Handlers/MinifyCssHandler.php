<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Handlers;


use Configs\DefaultConfig;
use CssMin;
use Exception;
use Exceptions\MinifyCssException;
use Phpfastcache\CacheManager;
use Phpfastcache\Helper\Psr16Adapter;

class MinifyCssHandler
{
    /**
     * @var MinifyCssHandler|null
     */
    private static $instance = null;

    /**
     * @var string
     */
    private static $md5checksum = "";

    /**
     * @var string
     */
    private $baseDir = "";

    /**
     * @var string
     */
    private $defaultMinifyCssDir = "";

    /**
     * @var string
     */
    private $defaultMinifyCssFile = "";

    /**
     * @var array
     */
    private $cssContent = [];

    /**
     * @var Psr16Adapter
     * @todo implement caching
     */
    private $cache;

    /**
     * MinifyCssHandler constructor.
     * @param DefaultConfig $config
     * @throws MinifyCssException
     */
    public function __construct(DefaultConfig $config)
    {
        $this->baseDir = $config->getBaseDir();

        $this->defaultMinifyCssDir = sprintf("%s/data/cache/css", $this->baseDir);

        if (!file_exists($this->defaultMinifyCssDir)) {
            if (!@mkdir($this->defaultMinifyCssDir, 0777, true)) {
                throw new MinifyCssException(sprintf("The required directory '%s' can not be created, please check the directory permissions or create it manually.", $this->defaultMinifyCssDir), E_ERROR);
            }
        }

        if (!is_writable($this->defaultMinifyCssDir)) {
            if (!@chmod($this->defaultMinifyCssDir, 0777)) {
                throw new MinifyCssException(sprintf("The required directory '%s' can not be written, please check the directory permissions.", $this->defaultMinifyCssDir), E_ERROR);
            }
        }
    }

    /**
     * @throws MinifyCssException
     */
    private function setDefaults()
    {
        if (is_null(self::$instance)) {
            throw new MinifyCssException("The class must be initiated first", E_ERROR);
        }

        $defaultCssPaths = array(
            sprintf("%s/bootstrap/css/bootstrap.min.css", $this->baseDir),
            sprintf("%s/assets/css/main.css", $this->baseDir),
            sprintf("%s/assets/css/plugins.css", $this->baseDir),
            sprintf("%s/assets/css/responsive.css", $this->baseDir),
            sprintf("%s/assets/css/icons.css", $this->baseDir)
        );

        foreach ($defaultCssPaths as $cssPath) {
            $this->addCss($cssPath);
        }
    }

    /**
     * @param DefaultConfig $config
     * @return MinifyCssHandler|null
     * @throws MinifyCssException
     */
    public static function init(DefaultConfig $config)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($config);
        }

        self::$instance->setDefaults();
        return self::$instance;
    }

    /**
     * @param bool $clearOldFiles
     * @return bool|int
     * @throws MinifyCssException
     */
    public function compileAndGet($clearOldFiles = true)
    {
        $this->defaultMinifyCssFile = sprintf("%s/%s.css", $this->defaultMinifyCssDir, md5(self::$md5checksum));

        if ($clearOldFiles) {
            $oldDate = time() - 3600;
            $cachedFiles = scandir($this->defaultMinifyCssDir);
            foreach ($cachedFiles as $file) {
                $filepath = sprintf("%s/%s", $this->defaultMinifyCssDir, $file);
                $fileMtime = @filemtime($filepath);
                if (strlen($file) == 35 && ($fileMtime === false || $fileMtime < $oldDate)) {
                    @unlink($filepath);
                }
            }
        }

        if (!file_exists($this->getDefaultMinifyCssFile())) {
            $content = "";
            foreach ($this->cssContent as $item) {
                $content .= is_file($item) ? file_get_contents($item) : trim($item);
            }

            try {
                return @file_put_contents($this->getDefaultMinifyCssFile(), CssMin::minify($content));
            } catch (Exception $e) {
                throw new MinifyCssException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return true;
    }

    /**
     * @param bool $relative
     * @return string|null
     */
    public function getDefaultMinifyCssFile($relative = false): ?string
    {
        return $relative ? substr(str_replace($this->baseDir, "", $this->defaultMinifyCssFile), 1) : $this->defaultMinifyCssFile;
    }

    /**
     * @param string $fileOrString
     * @param bool $codeAsString
     * @throws MinifyCssException
     */
    public function addCss(string $fileOrString, $codeAsString = false)
    {
        if ($codeAsString) {
            self::$md5checksum .= trim(md5($fileOrString));
        } elseif (!file_exists($fileOrString)) {
            throw new MinifyCssException(sprintf("The file '%s' does not exist, please check directory manually", $fileOrString), E_ERROR);
        } elseif (!is_readable($fileOrString)) {
            throw new MinifyCssException(sprintf("The file '%s' can not be loaded, please check the file permissions", $fileOrString), E_ERROR);
        } else {
            $fileMtime = @filemtime($fileOrString);
            self::$md5checksum .= date('YmdHis', $fileMtime ? $fileMtime : NULL) . $fileOrString;
        }

        $this->cssContent[] = $fileOrString;
    }
}