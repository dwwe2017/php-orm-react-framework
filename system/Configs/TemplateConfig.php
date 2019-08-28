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
     * @see ModuleManager::__construct()
     * @param DefaultConfig $defaultConfig
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
            ]
        ];
    }
}