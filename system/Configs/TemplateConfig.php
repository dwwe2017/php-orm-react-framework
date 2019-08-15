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
use Interfaces\ConfigInterfaces\VendorExtensionConfigInterface;

/**
 * Class TemplateConfig
 * @package Configs
 */
class TemplateConfig implements VendorExtensionConfigInterface
{
    /**
     * @var self|null
     */
    public static $instance = null;

    /**
     * @var string
     */
    private static $instanceKey = "";

    /**
     * @var ConfigValues
     */
    private $config;

    /**
     * @var ConfigValues
     */
    private $configValues = null;

    /**
     * TemplateConfig constructor.
     * @param ConfigValues $config
     * @throws TemplateException
     */
    public function __construct(ConfigValues $config)
    {
        $this->config = $config;

        $config = ["template_options" => $this->config->get("template_options", [])];
        $config = ConfigFactory::fromArray($this->getOptionsDefault())->mergeValues($config);

        $cache = $config->get("template_options.cache", false);

        if ($cache !== false && !file_exists($cache)) {
            if (!@mkdir($config->get("cache"), 0777, true)) {
                throw new TemplateException(sprintf("The required directory '%s' for template compilation can not be found and/or be created, please check the directory permissions or create it manually.", $cache), E_ERROR);
            }
        }

        if ($cache !== false && !is_writable($cache)) {
            if (!@chmod($config->get("cache"), 0777)) {
                throw new TemplateException(sprintf("The required directory '%s' for template compilation can not be written, please check the directory permissions.", $cache), E_ERROR);
            }
        }

        $this->configValues = $config;
    }

    /**
     * @param ConfigValues $config
     * @return ConfigValues
     * @throws TemplateException
     */
    public static function init(ConfigValues $config): ConfigValues
    {
        if (is_null(self::$instance) || serialize(self::$instance) !== self::$instanceKey) {
            self::$instance = new self($config);
            self::$instanceKey = serialize(self::$instance);
        }

        return self::$instance->configValues;
    }

    /**
     * @return array
     */
    public function getOptionsDefault(): array
    {
        $isDebug = $this->config->get("debug_mode");
        $baseDir = $this->config->get("base_dir");
        $cacheDir = sprintf("%s/data/cache/compilation", $baseDir);

        return [
            "template_options" => [
                "debug" => $isDebug,
                "base_dir" => $baseDir,
                "template" => "default",
                "charset " => "utf-8",
                "base_template_class" => "\\Twig\\Template",
                "cache" => $isDebug ? false : $cacheDir,
                "auto_reload" => !$isDebug,
                "strict_variables" => $isDebug,
                "autoescape" => "html",
                "optimizations" => $isDebug ? 0 : -1,
            ]
        ];
    }
}