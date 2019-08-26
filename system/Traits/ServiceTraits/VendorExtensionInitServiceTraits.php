<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Traits\ServiceTraits;


use Configula\ConfigValues;
use Controllers\AbstractBase;
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