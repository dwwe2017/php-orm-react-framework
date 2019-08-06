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

/**
 * Interface DoctrineConfigInterface
 * @package Interfaces\ConfigInterfaces
 */
interface DoctrineConfigInterface
{
    public function __construct(DefaultConfig $config, $connectionOption = "default");

    public static function init(DefaultConfig $config, $connectionOption = "default");
}