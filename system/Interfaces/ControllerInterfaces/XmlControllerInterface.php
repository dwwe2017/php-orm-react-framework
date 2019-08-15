<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Interfaces\ControllerInterfaces;

/**
 * Interface XmlControllerInterface
 * @package Interfaces\ControllerInterfaces
 */
interface XmlControllerInterface
{
    const HEADER_CONTENT_TYPE_JSON = "Content-type: application/json; charset=utf-8";

    const HEADER_ERROR_404 = "HTTP/1.0 404 Not Found";

    public function indexAction(): void;
}