<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Traits\ConfigTraits;


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
    private $configValues = null;

    /**
     * @var ConfigValues
     */
    private $config;

    /**
     * VendorExtensionInitConfigTrait constructor.
     * @param ConfigValues $config
     */
    public function __construct(ConfigValues $config)
    {

    }

    /**
     * @param ConfigValues $config
     * @return ConfigValues
     */
    public static function init(ConfigValues $config): ConfigValues
    {
        if (is_null(self::$instance) || serialize($config) !== self::$instanceKey) {
            self::$instance = new self($config);
            self::$instanceKey = serialize($config);
        }

        return self::$instance->configValues;
    }
}