<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Interfaces\ExceptionInterfaces;


use Throwable;

/**
 * Interface CustomExceptionInterface
 * @package Interfaces\ExceptionInterfaces
 */
interface CustomExceptionInterface
{
    public function __construct($message = "", $code = 0, Throwable $previous = null);
}