<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Handlers;


use Configula\ConfigValues;
use CssMin;
use Exception;
use Exceptions\MinifyCssException;
use Helpers\FileHelper;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

class MinifyCssHandler
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var string
     */
    private static $md5checksum = "";

    /**
     * @var string
     */
    private $baseDir = "";

    /**
     * @var array
     */
    private $defaultCssPaths = [];

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
     * MinifyCssHandler constructor.
     * @param ConfigValues $config
     */
    private final function __construct(ConfigValues $config)
    {
        $this->baseDir = $config->get("base_dir");
        $this->defaultCssPaths = $config->get("default_css", []);
        $this->defaultMinifyCssDir = sprintf("%s/data/cache/css", $this->baseDir);

        FileHelper::init($this->defaultMinifyCssDir, MinifyCssException::class)
            ->isWritable(true);
    }

    /**
     *
     */
    private function setDefaults()
    {
        foreach ($this->defaultCssPaths as $cssPath) {
            $this->addCss($cssPath);
        }
    }

    /**
     * @param ConfigValues $config
     * @return MinifyCssHandler|null
     */
    public static final function init(ConfigValues $config)
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
     * @throws MinifyCssException
     */
    public final function compileAndGet($clearOldFiles = true)
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
                $content .= strlen($item) < 999 && is_file($item) ? file_get_contents($item) : trim($item);
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
    public final function getDefaultMinifyCssFile($relative = false): ?string
    {
        return $relative ? substr(str_replace($this->baseDir, "", $this->defaultMinifyCssFile), 1) : $this->defaultMinifyCssFile;
    }

    /**
     * @param string|null $fileOrString
     * @param bool $codeAsString
     */
    public final function addCss(?string $fileOrString, $codeAsString = false): void
    {
        if (is_null($fileOrString)) {
            return;
        }

        if ($codeAsString || strcasecmp(substr($fileOrString, -4), ".css") != 0) {
            self::$md5checksum .= trim(md5($fileOrString));
        } elseif (strcasecmp(substr($fileOrString, 0, 4), "http") == 0) {
            $fileOrString = @file_get_contents($fileOrString);
            self::$md5checksum .= trim(md5($fileOrString));
        } else {
            FileHelper::init($fileOrString, MinifyCssException::class)->isReadable();
            $fileMtime = @filemtime($fileOrString);
            self::$md5checksum .= date('YmdHis', $fileMtime ? $fileMtime : NULL) . $fileOrString;
        }

        $this->cssContent[] = $fileOrString;
    }

    /**
     * @param array $cssContent
     */
    public final function setCssContent(array $cssContent): void
    {
        $this->cssContent = [];

        foreach ($cssContent as $item) {
            $this->addCss($item);
        }
    }
}