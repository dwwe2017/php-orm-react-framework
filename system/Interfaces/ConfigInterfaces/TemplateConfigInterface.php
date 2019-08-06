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
 * Interface TemplateConfigInterface
 * @package Interfaces\ConfigInterfaces
 */
interface TemplateConfigInterface
{
    public function __construct(DefaultConfig $config, ?string $moduleViewsDir = "modules", string $defaultTemplatesDir = "templates", string $defaultViewsDir = "views");

    public static function init(DefaultConfig $config, ?string $moduleViewsDir = "modules", string $defaultTemplatesDir = "templates", string $defaultViewsDir = "views");
}