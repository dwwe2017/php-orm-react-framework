<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Handlers;


use Configs\CoreConfig;
use Exceptions\MinifyJsException;
use MatthiasMullie\Minify\JS;

class MinifyJsHandler extends JS
{
    /**
     * @var MinifyJsHandler|JS|null
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
    private $defaultMinifyJsDir = "";

    /**
     * @var string
     */
    private $defaultMinifyJsFile = "";

    /**
     * @var array
     */
    private $jsData = [];

    /**
     * @noinspection PhpMissingParentConstructorInspection
     * MinifyJsHandler constructor.
     * @param CoreConfig $config
     * @throws MinifyJsException
     */
    public function __construct($config)
    {
        if ($config instanceof CoreConfig) {
            $this->baseDir = $config->getBaseDir();

            $this->defaultMinifyJsDir = sprintf("%s/data/cache/js", $this->baseDir);

            $this->defaultMinifyJsFile = sprintf("%s/minified.js", $this->defaultMinifyJsDir);

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
        } else {
            parent::__construct($config);
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
            $this->addJs($jsPath);
        }

        $this->addJs(
            "$(document).ready(function(){
                \"use strict\";

                App.init(); // Init layout and core plugins
                Plugins.init(); // Init all plugins
                FormComponents.init(); // Init all form-specific plugins
            });", true
        );
    }

    /**
     * @param CoreConfig $config
     * @return MinifyJsHandler|JS|null
     * @throws MinifyJsException
     */
    public static function init(CoreConfig $config)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($config);
        }

        self::$instance->setDefaults();
        return self::$instance;
    }

    /**
     * @return string
     */
    public function compileAndGet()
    {
        $this->defaultMinifyJsFile = sprintf("%s/%s.js", $this->defaultMinifyJsDir, md5(self::$md5checksum));

        if (!file_exists($this->getDefaultMinifyJsFile())) {
            foreach ($this->jsData as $jsPath) {
                $this->add($jsPath);
            }
        }

        if (!file_exists($this->getDefaultMinifyJsFile())) {
            return $this->minify($this->getDefaultMinifyJsFile());
        }

        return true;
    }

    /**
     * @param bool $relative
     * @return string
     */
    public function getDefaultMinifyJsFile($relative = false): string
    {
        return $relative ? substr(str_replace($this->baseDir, "", $this->defaultMinifyJsFile), 1) : $this->defaultMinifyJsFile;
    }

    /**
     * @param string $fileOrString
     * @param bool $codeAsString
     * @throws MinifyJsException
     */
    public function addJs(string $fileOrString, $codeAsString = false)
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

        $this->jsData[] = $fileOrString;
    }
}