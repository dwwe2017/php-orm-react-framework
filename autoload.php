<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

$baseDir = __DIR__;

/**
 * @var $classLoader Composer\Autoload\ClassLoader
 */
$classLoader = require_once __DIR__ . '/vendor/autoload.php';

use Handlers\AutoloadHandler;

/**
 * Dynamic autoloader for annotations, modules and their namespaces
 */
AutoloadHandler::init($baseDir, $classLoader)->register();