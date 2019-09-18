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
        $this->defaultMinifyJsDir = sprintf("%s/data/cache/js", $this->baseDir);

        FileHelper::init($this->defaultMinifyJsDir, MinifyJsException::class)
            ->isWritable(true);
    }

    /**
     *
     */
    private function setDefaults()
    {
        $defaultJsPaths = array(
            //jQuery
            sprintf("%s/assets/js/libs/jquery-3.4.1.min.js", $this->baseDir),
            sprintf("%s/plugins/jquery-ui/jquery-ui-1.10.2.custom.min.js", $this->baseDir),
            //Bootstrap
            sprintf("%s/bootstrap/js/bootstrap.js", $this->baseDir),
            sprintf("%s/assets/js/libs/lodash.compat.min.js", $this->baseDir),
            //Smartphone Touch Events
            sprintf("%s/plugins/touchpunch/jquery.ui.touch-punch.min.js", $this->baseDir),
            sprintf("%s/plugins/event.swipe/jquery.event.move.js", $this->baseDir),
            sprintf("%s/plugins/event.swipe/jquery.event.swipe.js", $this->baseDir),
            //General
            sprintf("%s/assets/js/libs/breakpoints.js", $this->baseDir),
            sprintf("%s/plugins/respond/respond.min.js", $this->baseDir),
            sprintf("%s/plugins/cookie/jquery.cookie.min.js", $this->baseDir),
            sprintf("%s/plugins/slimscroll/jquery.slimscroll.min.js", $this->baseDir),
            sprintf("%s/plugins/slimscroll/jquery.slimscroll.horizontal.min.js", $this->baseDir),
            //Charts
            sprintf("%s/plugins/sparkline/jquery.sparkline.min.js", $this->baseDir),
            sprintf("%s/plugins/daterangepicker/moment.min.js", $this->baseDir),
            sprintf("%s/plugins/daterangepicker/daterangepicker.js", $this->baseDir),
            sprintf("%s/plugins/blockui/jquery.blockUI.min.js", $this->baseDir),
            //Forms
            sprintf("%s/plugins/uniform/jquery.uniform.min.js", $this->baseDir),
            sprintf("%s/plugins/select2/select2.min.js", $this->baseDir),
            //DataTables
            sprintf("%s/plugins/datatables/jquery.dataTables.min.js", $this->baseDir),
            sprintf("%s/plugins/datatables/DT_bootstrap.js", $this->baseDir),
            sprintf("%s/plugins/datatables/responsive/datatables.responsive.js", $this->baseDir),
            //Application
            sprintf("%s/assets/js/app.js", $this->baseDir),
            sprintf("%s/assets/js/plugins.js", $this->baseDir),
            sprintf("%s/assets/js/plugins.form-components.js", $this->baseDir),
            sprintf("%s/assets/js/system.js", $this->baseDir),
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
    public final function compileAndGet($clearOldFiles = true)
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
    public final function getDefaultMinifyJsFile($relative = false): ?string
    {
        return $relative ? substr(str_replace($this->baseDir, "", $this->defaultMinifyJsFile), 1) : $this->defaultMinifyJsFile;
    }

    /**
     * @param string $fileOrString
     * @param bool $codeAsString
     */
    public final function addJsContent(string $fileOrString, $codeAsString = false)
    {
        if ($codeAsString) {
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