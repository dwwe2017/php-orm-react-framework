<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Traits\ConfigTraits;


use Configs\DefaultConfig;
use Configula\ConfigValues;

/**
 * Trait VendorExtensionInitConfigTrait
 * @package Traits\ConfigTraits
 */
trait VendorExtensionInitConfigTrait
{
    /**
     * @var ConfigValues
     */
    private ?ConfigValues $configValues = null;

    /**
     * @var ConfigValues
     */
    private ?ConfigValues $config;

    /**
     * self constructor.
     * @param DefaultConfig $defaultConfig
     */
    public function __construct(DefaultConfig $defaultConfig)
    {

    }

    /**
     * @param DefaultConfig $defaultConfig
     * @return ConfigValues
     */
    public static function init(DefaultConfig $defaultConfig): ConfigValues
    {
        if (is_null(self::$instance) || serialize($defaultConfig) !== self::$instanceKey) {
            self::$instance = new self($defaultConfig);
            self::$instanceKey = serialize($defaultConfig);
        }

        return self::$instance->configValues;
    }
}