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
use Helpers\FileHelper;
use Interfaces\ConfigInterfaces\ApplicationConfigInterface;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class DefaultConfig
 * @package Configs Revised and added options of the configuration file
 * @see ModuleManager::$cacheConfig
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
     */
    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;

        $configDir = sprintf("%s/config", $this->baseDir);
        FileHelper::init($configDir, ConfigException::class)->isReadable();

        $this->configValues = ConfigFactory::loadSingleDirectory($configDir, $this->getOptionsDefault());
    }

    /**
     * @param string $baseDir
     * @return ConfigValues
     */
    public static function init(string $baseDir): ConfigValues
    {
        if (is_null(self::$instance) || serialize($baseDir) !== self::$instanceKey) {
            self::$instance = new self($baseDir);
            self::$instanceKey = serialize($baseDir);
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
            "language" => "en_US",
            //Database configuration
            "connection_options" => [],
            //Doctrine configuration
            "doctrine_options" => [],
            //Template configuration
            "template_options" => [],
            //Logger configuration
            "logger_options" => [],
            //PhpFastCache configuration
            "cache_options" => [
                "driver" => [
                    "driverName" => "files"
                ]
            ]
        ];
    }
}