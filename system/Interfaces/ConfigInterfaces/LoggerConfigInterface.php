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
use Monolog\Logger;

/**
 * Interface LoggerConfigInterface
 * @package Interfaces\ConfigInterfaces
 */
interface LoggerConfigInterface
{
    const DEBUG = Logger::DEBUG;

    const INFO = Logger::INFO;

    const NOTICE = Logger::NOTICE;

    const WARNING = Logger::WARNING;

    const ERROR = Logger::ERROR;

    const CRITICAL = Logger::CRITICAL;

    const ALERT = Logger::ALERT;

    const EMERGENCY = Logger::EMERGENCY;

    public function __construct(DefaultConfig $config, $level = self::ERROR, $application = "tsi");

    public static function init(DefaultConfig $config, $level = self::ERROR, $application = "tsi");
}