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
use Exceptions\ConfigException;
use Interfaces\ConfigInterfaces\ApplicationConfigInterface;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class DefaultConfig
 * @package Configs
 */
class DefaultConfig implements ApplicationConfigInterface
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var string
     */
    private $baseDir = "";

    /**
     * @var ConfigValues
     */
    private $configValues = null;

    /**
     * DefaultConfig constructor.
     * @param string $baseDir
     * @throws ConfigException
     */
    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;

        $configDir = sprintf("%s/config", $this->baseDir);

        if (!file_exists($configDir)) {
            throw new ConfigException(sprintf("The config directory '%s' is missing", $configDir), E_ERROR);
        } elseif (!is_readable($configDir)) {
            throw new ConfigException(sprintf("The config directory '%s' can not be loaded, please check the directory permissions", $configDir), E_ERROR);
        }

        $this->configValues = ConfigFactory::loadSingleDirectory($configDir, $this->getOptionsDefault());
    }

    /**
     * @param string $baseDir
     * @return ConfigValues
     * @throws ConfigException
     */
    public static function init(string $baseDir): ConfigValues
    {
        if (is_null(self::$instance) || serialize(self::$instance) !== self::$instanceKey) {
            self::$instance = new self($baseDir);
            self::$instanceKey = serialize(self::$instance);
        }

        return self::$instance->configValues;
    }

    /**
     * @return array
     */
    public function getOptionsDefault(): array
    {
        return [
            //General properties
            "debug_mode" => false,
            "base_dir" => $this->baseDir,
            //Database configuration
            "connection_options" => [],
            //Doctrine configuration
            "doctrine_options" => [],
            //Template configuration
            "template_options" => [],
            //Logger configuration
            "logger_options" => [],
            //PhpFastCache configuration
            "cache_options" => []
        ];
    }
}