<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Interfaces\ConfigInterfaces;


/**
 * Interface DefaultConfigInterface
 * @package Interfaces\ConfigInterfaces
 */
interface DefaultConfigInterface
{
    public function __construct(string $base_dir);

    public static function init(string $base_dir);
}