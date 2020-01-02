<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Configs;


use Configula\ConfigFactory;
use Exceptions\TemplateException;
use Helpers\FileHelper;
use Interfaces\ConfigInterfaces\VendorExtensionConfigInterface;
use Traits\ConfigTraits\VendorExtensionInitConfigTrait;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class TemplateConfig
 * @package Configs Revised and added options of the configuration file
 * @see ModuleManager::$templateConfig
 */
class TemplateConfig implements VendorExtensionConfigInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitConfigTrait;

    /**
     * TemplateConfig constructor.
     * @param DefaultConfig $defaultConfig
     * @see ModuleManager::__construct()
     */
    public final function __construct(DefaultConfig $defaultConfig)
    {
        $this->config = $defaultConfig->getConfigValues();
        $baseDir = $this->config->get("base_dir");

        /**
         * Build template options
         */
        $tplConfig = ["template_options" => $this->config->get("template_options", [])];
        $tplConfig = ConfigFactory::fromArray($this->getOptionsDefault())->mergeValues($tplConfig);

        /**
         * Create and check paths if necessary
         */
        $cacheDir = $tplConfig->get("template_options.cache", false);

        if ($cacheDir !== false) {
            $cacheDir = sprintf("%s/%s", $baseDir, $cacheDir);
            FileHelper::init($cacheDir, TemplateException::class)->isWritable(true);

            $tplConfig = $tplConfig->mergeValues([
                "template_options" => [
                    "cache" => $cacheDir
                ]
            ]);
        }

        /**
         * Check and factory default JS files
         */
        $defaultJsFiles = [];
        $jsFiles = $tplConfig->get("default_js", []);

        if (!empty($jsFiles)) {
            foreach ($jsFiles as $jsFile) {
                if ((strcasecmp(substr($jsFile, 0, 4), "http") == 0)
                    || (strcasecmp(substr($jsFile, -3), ".js") != 0)) {
                    $defaultJsFiles[] = $jsFile;
                    continue;
                }

                $jsFile = sprintf("%s/%s", $baseDir, $jsFile);
                if (FileHelper::init($jsFile, TemplateException::class)->isReadable()) {
                    $defaultJsFiles[] = $jsFile;
                }
            }

            $tplConfig = $tplConfig->mergeValues([
                "default_js" => $defaultJsFiles
            ]);
        }

        /**
         * Check and factory default CSS files
         */
        $defaultCssFiles = [];
        $cssFiles = $tplConfig->get("default_css", []);

        if (!empty($cssFiles)) {
            foreach ($cssFiles as $cssFile) {
                if ((strcasecmp(substr($cssFile, 0, 4), "http") == 0)
                    || (strcasecmp(substr($cssFile, -4), ".css") != 0)) {
                    $defaultCssFiles[] = $cssFile;
                    continue;
                }

                $cssFile = sprintf("%s/%s", $baseDir, $cssFile);
                if (FileHelper::init($cssFile, TemplateException::class)->isReadable()) {
                    $defaultCssFiles[] = $cssFile;
                }
            }

            $tplConfig = $tplConfig->mergeValues([
                "default_css" => $defaultCssFiles
            ]);
        }

        /**
         * Finished
         */
        $this->configValues = $tplConfig;
    }

    /**
     * @return array
     */
    public final function getOptionsDefault(): array
    {
        $isDebug = $this->config->get("debug_mode");

        return [
            "template_options" => [
                "debug" => $isDebug,
                "template" => "default",
                "charset " => "utf-8",
                "base_template_class" => "\\Twig\\Template",
                "cache" => $isDebug ? false : "data/cache/compilation",
                "auto_reload" => !$isDebug,
                "strict_variables" => $isDebug,
                "autoescape" => "html",
                "optimizations" => $isDebug ? 0 : -1,
            ],
            "default_js" => [
                "assets/js/libs/jquery-3.4.1.min.js",
                "assets/js/plugins/jquery-ui/jquery-ui-1.10.2.custom.min.js",
                //Bootstrap
                "assets/js/libs/bootstrap.js",
                "assets/js/libs/lodash.compat.min.js",
                //Smartphone Touch Events
                "assets/js/plugins/touchpunch/jquery.ui.touch-punch.min.js",
                "assets/js/plugins/event.swipe/jquery.event.move.js",
                "assets/js/plugins/event.swipe/jquery.event.swipe.js",
                //General
                "assets/js/libs/breakpoints.js",
                "assets/js/plugins/respond/respond.min.js",
                "assets/js/plugins/cookie/jquery.cookie.min.js",
                "assets/js/plugins/slimscroll/jquery.slimscroll.min.js",
                "assets/js/plugins/slimscroll/jquery.slimscroll.horizontal.min.js",
                //Charts
                "assets/js/plugins/sparkline/jquery.sparkline.min.js",
                "assets/js/plugins/daterangepicker/moment.min.js",
                "assets/js/plugins/daterangepicker/daterangepicker.js",
                "assets/js/plugins/blockui/jquery.blockUI.js",
                //Forms
                "assets/js/plugins/uniform/jquery.uniform.min.js",
                "assets/js/plugins/select2/select2.min.js",
                //DataTables
                "assets/js/plugins/datatables/jquery.dataTables.min.js",
                "assets/js/plugins/datatables/DT_bootstrap.js",
                "assets/js/plugins/datatables/responsive/datatables.responsive.js",
                //Notifications
                "assets/js/plugins/noty/jquery.noty.js",
                "assets/js/plugins/noty/layouts/top.js",
                "assets/js/plugins/noty/themes/default.js",
                //Application
                "assets/js/app.js",
                "assets/js/plugins.js",
                "assets/js/plugins.form-components.js",
                "assets/js/system.js",
                "$(document).ready(function(){
                    \"use strict\";
    
                    App.init(); // Init layout and core plugins
                    Plugins.init(); // Init all plugins
                    FormComponents.init(); // Init all form-specific plugins
                });"
            ],
            "default_css" => [
                //Bootstrap
                "assets/css/bootstrap.min.css",
                "assets/css/main.css",
                //Plugins
                "assets/css/plugins/bootstrap-colorpicker.css",
                "assets/css/plugins/bootstrap-multiselect.css",
                "assets/css/plugins/bootstrap-switch.css",
                "assets/css/plugins/bootstrap-wizard.css",
                "assets/css/plugins/bootstrap-wysihtml5.css",
                "assets/css/plugins/datatables.css",
                "assets/css/plugins/datatables_bootstrap.css",
                "assets/css/plugins/daterangepicker.css",
                "assets/css/plugins/duallistbox.css",
                "assets/css/plugins/fullcalendar.css",
                "assets/css/plugins/jquery-ui.css",
                "assets/css/plugins/nestable.css",
                "assets/css/plugins/nprogress.css",
                "assets/css/plugins/pickadate.css",
                "assets/css/plugins/select2.css",
                "assets/css/plugins/tagsinput.css",
                "assets/css/plugins/typeahead.css",
                "assets/css/plugins/uniform.css",
                //General
                "assets/css/responsive.css",
                "assets/css/icons.css"
            ],
        ];
    }
}