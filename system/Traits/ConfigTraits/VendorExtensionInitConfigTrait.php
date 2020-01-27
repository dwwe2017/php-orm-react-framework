<?php

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