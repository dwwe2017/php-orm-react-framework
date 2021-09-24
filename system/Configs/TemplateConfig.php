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
        $defaultJsFiles = $this->config->get("default_js", []);
        $jsFiles = empty($defaultJsFiles) ? $tplConfig->get("default_js", []) : $defaultJsFiles;

        if (!empty($jsFiles)) {
            $defaultJsFiles = [];
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
        $defaultCssFiles = $this->config->get("default_css", []);
        $cssFiles = empty($defaultCssFiles) ? $tplConfig->get("default_css", []) : $defaultCssFiles;

        if (!empty($cssFiles)) {
            $defaultCssFiles = [];
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
         * Remove default non minified js source if set manually
         */
        $defaultNonMinifiedJs = $this->config->get("default_non_minified_js", []);
        $tplConfig = $tplConfig->mergeValues([
            "default_non_minified_js" => empty($defaultNonMinifiedJs) ? $tplConfig->get("default_non_minified_js", []) : $defaultNonMinifiedJs
        ]);

        /**
         * Remove default non minified css source if set manually
         */
        $defaultNonMinifiedCss = $this->config->get("default_non_minified_css", []);
        $tplConfig = $tplConfig->mergeValues([
            "default_non_minified_css" => empty($defaultNonMinifiedCss) ? $tplConfig->get("default_non_minified_css", []) : $defaultNonMinifiedCss
        ]);

        /**
         * Remove default CDN js source if set manually
         */
        $defaultCdnJs = $this->config->get("default_cdn_js", []);
        $tplConfig = $tplConfig->mergeValues([
            "default_cdn_js" => empty($defaultCdnJs) ? $tplConfig->get("default_cdn_js", []) : $defaultCdnJs
        ]);

        /**
         * Remove default CDN css source if set manually
         */
        $defaultCdnCss = $this->config->get("default_cdn_css", []);
        $tplConfig = $tplConfig->mergeValues([
            "default_cdn_css" => empty($defaultCdnCss) ? $tplConfig->get("default_cdn_css", []) : $defaultCdnCss
        ]);

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
                "template" => "coreui",
                "charset " => "utf-8",
                "base_template_class" => "\\Twig\\Template",
                "cache" => $isDebug ? false : "data/cache/compilation",
                "auto_reload" => !$isDebug,
                "strict_variables" => $isDebug,
                "autoescape" => "html",
                "optimizations" => $isDebug ? 0 : -1,
            ],
            "default_js" => [],
            "default_css" => [],
            "default_non_minified_js" => [],
            "default_non_minified_css" => [],
            "default_cdn_js" => [
                [
                    /**
                     * @see https://www.jsdelivr.com/package/npm/@coreui/coreui
                     * @author @coreui/coreui
                     * @file coreui.bundle.min.js
                     * @version 3.4.0
                     */
                    "href" => "https://cdn.jsdelivr.net/npm/@coreui/coreui@3.4.0/dist/js/coreui.bundle.min.js",
                    "integrity" => "sha256-pNVhsgAxflakVHYrSm+g0qX/Mg/OozmqIPlcA/UmWaY=",
                    "crossorigin" => "anonymous"
                ],
                [
                    /**
                     * @see https://www.jsdelivr.com/package/npm/@coreui/chartjs?version=2.0.0&path=dist%2Fjs
                     * @author @coreui/chartjs
                     * @version 2.0.0
                     * @file coreui-chartjs.min.js
                     */
                    "href" => "https://cdn.jsdelivr.net/npm/@coreui/chartjs@2.0.0/dist/js/coreui-chartjs.min.js",
                    "integrity" => "sha256-BYNHBo+f3ti8HRrA9Gr55e5wo5qeZVzZJheEjPAgmaw=",
                    "crossorigin" => "anonymous"
                ],
                [
                    /**
                     * @see https://www.jsdelivr.com/package/npm/@coreui/utils?path=dist
                     * @author @coreui/utils
                     * @version 1.3.1
                     * @file coreui-utils.js
                     */
                    "href" => "https://cdn.jsdelivr.net/npm/@coreui/utils@1.3.1/dist/coreui-utils.js",
                    "integrity" => "sha256-NVrkdvRh8oXb52THPYm46LAZWIqzJKxlJYaN6p3PzHk=",
                    "crossorigin" => "anonymous"
                ]
            ],
            "default_cdn_css" => [
                [
                    /**
                     * @see https://www.jsdelivr.com/package/npm/@coreui/coreui
                     * @author @coreui/coreui
                     * @file coreui.min.css
                     * @version 3.4.0
                     */
                    "href" => "https://cdn.jsdelivr.net/npm/@coreui/coreui@3.4.0/dist/css/coreui.min.css",
                    "integrity" => "sha256-ymLt+ThGD+jSN1VPjDdI1onY9UVinS39bJuWRzM94t8=",
                    "crossorigin" => "anonymous"
                ],
                [
                    /**
                     * @see https://www.jsdelivr.com/package/npm/@coreui/chartjs?version=2.0.0&path=dist%2Fcss
                     * @author @coreui/chartjs
                     * @version 2.0.0
                     * @file coreui-chartjs.min.css
                     */
                    "href" => "https://cdn.jsdelivr.net/npm/@coreui/chartjs@2.0.0/dist/css/coreui-chartjs.min.css",
                    "integrity" => "sha256-r+WaegrEE/v+hab/ZL7pfs8DbAfvyYM0F9atxcLYnn8=",
                    "crossorigin" => "anonymous"
                ],
                [
                    /**
                     * @link https://www.jsdelivr.com/package/npm/@coreui/icons?path=css
                     * @author @coreui/icons
                     * @file all.min.css
                     * @version 2.0.1
                     */
                    "href" => "https://cdn.jsdelivr.net/npm/@coreui/icons@2.0.1/css/all.min.css",
                    "integrity" => "sha256-W6Lexo8XTtkIn8nOCBocGu6Ty3ZZnraK550Ie8iuLAg=",
                    "crossorigin" => "anonymous"
                ],
                [
                    /**
                     * @link https://www.jsdelivr.com/package/npm/@coreui/icons?path=css
                     * @author @coreui/icons
                     * @file brand.min.css
                     * @version 2.0.1
                     * @notice Use cib- prefix for linear icons
                     */
                    "href" => "https://cdn.jsdelivr.net/npm/@coreui/icons@2.0.1/css/brand.min.css",
                    "integrity" => "sha256-5iRAOmCdbiRkYuvul6+RXXt8VvbgJ7P2kxvABrWa1jk=",
                    "crossorigin" => "anonymous"
                ],
                [
                    /**
                     * @link https://www.jsdelivr.com/package/npm/@coreui/icons?path=css
                     * @author @coreui/icons
                     * @file flag.min.css
                     * @version 2.0.1
                     * @notice Use cif- prefix for linear icons
                     */
                    "href" => "https://cdn.jsdelivr.net/npm/@coreui/icons@2.0.1/css/flag.min.css",
                    "integrity" => "sha256-vPcHAKo5V7+PU63JSYsUaudKZdfLYkFobV8ssUm8yg8=",
                    "crossorigin" => "anonymous"
                ],
                [
                    /**
                     * @link https://www.jsdelivr.com/package/npm/@coreui/icons?path=css
                     * @author @coreui/icons
                     * @file free.min.css
                     * @version 2.0.1
                     * @notice Use cil- prefix for linear icons
                     */
                    "href" => "https://cdn.jsdelivr.net/npm/@coreui/icons@2.0.1/css/free.min.css",
                    "integrity" => "sha256-6uqhzNLi3RU8WNmSSpqxMHkwlXFSNJH+H8L5rb4XQTg=",
                    "crossorigin" => "anonymous"
                ]
            ]
        ];
    }
}