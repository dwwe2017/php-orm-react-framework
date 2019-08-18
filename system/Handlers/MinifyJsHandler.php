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
use JShrink\Minifier;
use Phpfastcache\Helper\Psr16Adapter;
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
     * @var Psr16Adapter
     * @todo implement caching
     */
    private $cache;

    /**
     * MinifyJsHandler constructor.
     * @param ConfigValues $config
     * @throws MinifyJsException
     */
    public function __construct(ConfigValues $config)
    {
        $this->baseDir = $config->get("base_dir");

        $this->defaultMinifyJsDir = sprintf("%s/data/cache/js", $this->baseDir);

        if (!file_exists($this->defaultMinifyJsDir)) {
            if (!@mkdir($this->defaultMinifyJsDir, 0777, true)) {
                throw new MinifyJsException(sprintf("The required directory '%s' can not be created, please check the directory permissions or create it manually.", $this->defaultMinifyJsDir), E_ERROR);
            }
        }

        if (!is_writable($this->defaultMinifyJsDir)) {
            if (!@chmod($this->defaultMinifyJsDir, 0777)) {
                throw new MinifyJsException(sprintf("The required directory '%s' can not be written, please check the directory permissions.", $this->defaultMinifyJsDir), E_ERROR);
            }
        }
    }

    /**
     * @throws MinifyJsException
     */
    private function setDefaults()
    {
        if (is_null(self::$instance)) {
            throw new MinifyJsException("The class must be initiated first", E_ERROR);
        }

        $defaultJsPaths = array(
            sprintf("%s/assets/js/libs/jquery-3.4.1.min.js", $this->baseDir),
            sprintf("%s/plugins/jquery-ui/jquery-ui-1.10.2.custom.min.js", $this->baseDir),
            sprintf("%s/bootstrap/js/bootstrap.min.js", $this->baseDir),
            sprintf("%s/assets/js/libs/lodash.compat.min.js", $this->baseDir),
            sprintf("%s/assets/js/libs/html5shiv.js", $this->baseDir),
            sprintf("%s/plugins/touchpunch/jquery.ui.touch-punch.min.js", $this->baseDir),
            sprintf("%s/plugins/event.swipe/jquery.event.move.js", $this->baseDir),
            sprintf("%s/plugins/event.swipe/jquery.event.swipe.js", $this->baseDir),
            sprintf("%s/assets/js/libs/breakpoints.js", $this->baseDir),
            sprintf("%s/plugins/respond/respond.min.js", $this->baseDir),
            sprintf("%s/plugins/cookie/jquery.cookie.min.js", $this->baseDir),
            sprintf("%s/plugins/slimscroll/jquery.slimscroll.min.js", $this->baseDir),
            sprintf("%s/plugins/slimscroll/jquery.slimscroll.horizontal.min.js", $this->baseDir),
            sprintf("%s/assets/js/app.js", $this->baseDir),
            sprintf("%s/assets/js/plugins.js", $this->baseDir),
            sprintf("%s/assets/js/plugins.form-components.js", $this->baseDir),
            sprintf("%s/plugins/slimscroll/jquery.slimscroll.min.js", $this->baseDir)
        );

        foreach ($defaultJsPaths as $jsPath) {
            $this->addJsContent($jsPath);
        }

        $this->addJsContent(
            "$(document).ready(function(){
                \"use strict\";

                App.init(); // Init layout and core plugins
                Plugins.init(); // Init all plugins
                FormComponents.init(); // Init all form-specific plugins
            });", true
        );
    }

    /**
     * @param ConfigValues $config
     * @return MinifyJsHandler|null
     * @throws MinifyJsException
     */
    public static function init(ConfigValues $config)
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
    public function compileAndGet($clearOldFiles = true)
    {
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
                $content .= is_file($item) ? file_get_contents($item) : trim($item);
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
    public function getDefaultMinifyJsFile($relative = false): ?string
    {
        return $relative ? substr(str_replace($this->baseDir, "", $this->defaultMinifyJsFile), 1) : $this->defaultMinifyJsFile;
    }

    /**
     * @param string $fileOrString
     * @param bool $codeAsString
     * @throws MinifyJsException
     */
    public function addJsContent(string $fileOrString, $codeAsString = false)
    {
        if ($codeAsString) {
            self::$md5checksum .= trim(md5($fileOrString));
        } elseif (!file_exists($fileOrString)) {
            throw new MinifyJsException(sprintf("The file '%s' does not exist, please check directory manually", $fileOrString), E_ERROR);
        } elseif (!is_readable($fileOrString)) {
            throw new MinifyJsException(sprintf("The file '%s' can not be loaded, please check the file permissions", $fileOrString), E_ERROR);
        } else {
            $fileMtime = @filemtime($fileOrString);
            self::$md5checksum .= date('YmdHis', $fileMtime ? $fileMtime : NULL) . $fileOrString;
        }

        $this->jsContent[] = $fileOrString;
    }
}