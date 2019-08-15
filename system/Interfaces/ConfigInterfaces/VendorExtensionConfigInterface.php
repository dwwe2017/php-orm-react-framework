<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Interfaces\ConfigInterfaces;


use Configula\ConfigValues;

/**
 * Interface VendorExtensionConfigInterface
 * @package Interfaces\ConfigInterfaces
 */
interface VendorExtensionConfigInterface
{
    public function __construct(ConfigValues $config);

    public static function init(ConfigValues $config): ConfigValues;

    public function getOptionsDefault(): array;
}