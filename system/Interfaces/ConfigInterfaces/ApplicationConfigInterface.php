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
 * Interface ApplicationConfigInterface
 * @package Interfaces\ConfigInterfaces
 */
interface ApplicationConfigInterface
{
    public function __construct(string $baseDir);

    public static function init(string $baseDir): ConfigValues;

    public function getOptionsDefault(): array;
}