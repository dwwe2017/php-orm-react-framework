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
    private static $md5checksum = "";

    /**
     * @var string
     */
    private $baseDir = "";

    /**
     * @var array
     */
    private $defaultJsPaths = [];

    /**
     * @var string
     */
    private $defaultMinifyJsDir = "";

    /**
     * @var string
     */
    private $defaultMinifyJsFile = "";

    /**
     * @var array
     */
    private $jsContent = [];

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
     * @throws MinifyJsException
     */
    public final function compile($clearOldFiles = true)
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
    public final function getDefaultMinifyJsFile($relative = false): ?string
    {
        return $relative ? substr(str_replace($this->baseDir, "", $this->defaultMinifyJsFile), 1) : $this->defaultMinifyJsFile;
    }

    /**
     * @param string|null $fileOrString
     * @param bool $codeAsString
     */
    public final function addJsContent(?string $fileOrString, $codeAsString = false): void
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
            self::$md5checksum .= date('YmdHis', $fileMtime ? $fileMtime : NULL) . $fileOrString;
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
