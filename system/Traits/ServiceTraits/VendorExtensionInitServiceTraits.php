<?php

namespace Traits\ServiceTraits;


use Managers\ModuleManager;

/**
 * Trait VendorExtensionInitServiceTraits
 * @package Traits\ServiceTraits
 */
trait VendorExtensionInitServiceTraits
{
    /**
     * VendorExtensionInitServiceTraits constructor.
     * @param ModuleManager|null $moduleManager
     */
    public function __construct(ModuleManager $moduleManager)
    {

    }

    /**
     * @param ModuleManager|null $moduleManager
     * @return self
     */
    public static function init(ModuleManager $moduleManager)
    {
        if (is_null(self::$instance) || serialize($moduleManager) !== self::$instanceKey) {
            self::$instance = new self($moduleManager);
            self::$instanceKey = serialize($moduleManager);
        }

        return self::$instance;
    }
}