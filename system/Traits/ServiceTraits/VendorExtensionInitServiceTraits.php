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
use Exception;

trait VendorExtensionInitServiceTraits
{
    /**
     * VendorExtensionInitServiceTraits constructor.
     * @param ConfigValues $config
     * @param AbstractBase|null $controllerInstance
     */
    public function __construct(ConfigValues $config, AbstractBase $controllerInstance = null)
    {

    }

    /**
     * @param ConfigValues $config
     * @param AbstractBase|null $controllerInstance
     * @return self
     */
    public static function init(ConfigValues $config, AbstractBase $controllerInstance = null)
    {
        if (is_null(self::$instance) || serialize(self::$instance) !== self::$instanceKey) {
            self::$instance = new self($config, $controllerInstance);
            self::$instanceKey = serialize(self::$instance);
        }

        return self::$instance;
    }
}