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
use Configula\ConfigValues;
use Exceptions\TemplateException;
use Helpers\FileHelper;
use Interfaces\ConfigInterfaces\VendorExtensionConfigInterface;
use Traits\ConfigTraits\VendorExtensionInitConfigTrait;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class TemplateConfig
 * @package Configs
 */
class TemplateConfig implements VendorExtensionConfigInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitConfigTrait;

    /**
     * TemplateConfig constructor.
     * @param ConfigValues $config
     * @throws TemplateException
     */
    public function __construct(ConfigValues $config)
    {
        $this->config = $config;
        $baseDir = $this->config->get("base_dir");

        $tplConfig = ["template_options" => $this->config->get("template_options", [])];
        $tplConfig = ConfigFactory::fromArray($this->getOptionsDefault())->mergeValues($tplConfig);

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

        $this->configValues = $tplConfig;
    }

    /**
     * @return array
     */
    public function getOptionsDefault(): array
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