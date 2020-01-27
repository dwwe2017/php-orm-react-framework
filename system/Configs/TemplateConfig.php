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
        ];
    }
}