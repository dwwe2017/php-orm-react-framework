<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Configs;


use Exceptions\ConfigException;
use Interfaces\ConfigInterfaces\DefaultConfigInterface;

/**
 * Class DefaultConfig
 * @package Configs
 */
class DefaultConfig implements DefaultConfigInterface
{
    /**
     * @var DefaultConfig|null
     */
    private static $instance;

    /**
     * @var string
     */
    private $baseDir = "";

    /**
     * @var bool
     */
    private $debugMode = false;

    /**
     * @var array|null
     */
    private $config = [];

    /**
     * @var string
     */
    private $configDefaultPath = "";

    /**
     * @var string
     */
    private $defaultVendorDir = "";

    /**
     * @var array
     */
    private $tsiOptions = [];

    /**
     * @var array
     */
    private $defaultTsiOptions = [
        "debug_mode" => false,
        "template" => "default"
    ];

    /**
     * Bootstrap constructor.
     * @param string $base_dir
     * @throws ConfigException
     */
    public function __construct(string $base_dir)
    {
        $this->baseDir = $base_dir;

        $this->defaultVendorDir = sprintf("%s/vendor", $this->baseDir);

        if(!file_exists($this->defaultVendorDir))
        {
            throw new ConfigException(sprintf("The directory '%s' does not exist, please check the installation manually", $this->defaultVendorDir), E_ERROR);
        }
        elseif(!is_readable($this->defaultVendorDir))
        {
            throw new ConfigException(sprintf("The directory '%s' can not be loaded, please check the directory permissions", $this->defaultVendorDir), E_ERROR);
        }

        $this->configDefaultPath = sprintf("%s/config/default-config.php", $this->baseDir);

        if(!file_exists($this->configDefaultPath))
        {
            throw new ConfigException(sprintf("The global configuration file '%s' is missing", $this->configDefaultPath), E_ERROR);
        }
        elseif(!is_readable($this->configDefaultPath))
        {
            throw new ConfigException(sprintf("The global configuration file '%s' can not be loaded, please check the directory permissions", $this->configDefaultPath), E_ERROR);
        }

        /** @noinspection PhpIncludeInspection */
        $this->config = include_once $this->configDefaultPath;

        $this->tsiOptions = $this->getProperties("tsi_options");

        $this->tsiOptions += $this->defaultTsiOptions;

        $this->debugMode = $this->tsiOptions["debug_mode"];
    }

    /**
     * @param string $base_dir
     * @return DefaultConfig|null
     * @throws ConfigException
     */
    public static function init(string $base_dir)
    {
        if (is_null(self::$instance)) {
            self::$instance = new DefaultConfig($base_dir);
        }

        return self::$instance;
    }

    /**
     * @param string $property
     * @return array
     */
    public function getProperties(string $property): array
    {
        if(!is_array($this->config))
        {
            return $this->config;
        }
        elseif(!key_exists($property, $this->config))
        {
            return $this->config;
        }
        else
        {
            return $this->config[$property];
        }
    }

    /**
     * @param $parent_key
     * @param $property
     * @return array|mixed
     */
    public function getPropertyFrom($parent_key, $property)
    {
        $result = $this->getProperties($parent_key);

        if(!empty($result) && key_exists($property, $result))
        {
            $result = $result[$property];
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getBaseDir(): string
    {
        return $this->baseDir;
    }

    /**
     * @return string
     */
    public function getDefaultVendorDir(): string
    {
        return $this->defaultVendorDir;
    }

    /**
     * @return array|null
     */
    public function getConfig(): ?array
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function getConfigDefaultPath(): string
    {
        return $this->configDefaultPath;
    }

    /**
     * @return array
     */
    public function getTsiOptions(): array
    {
        return $this->tsiOptions;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getTsiOptionsProperty(string $key)
    {
        if(key_exists($key, $this->tsiOptions))
        {
            return $this->tsiOptions[$key];
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }
}