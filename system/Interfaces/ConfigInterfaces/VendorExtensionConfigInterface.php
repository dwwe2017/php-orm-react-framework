<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Interfaces\ConfigInterfaces;


use Configs\DefaultConfig;
use Configula\ConfigValues;

/**
 * Interface VendorExtensionConfigInterface
 * @package Interfaces\ConfigInterfaces
 */
interface VendorExtensionConfigInterface
{
    public function __construct(DefaultConfig $config);

    public static function init(DefaultConfig $config): ConfigValues;

    public function getOptionsDefault(): array;
}