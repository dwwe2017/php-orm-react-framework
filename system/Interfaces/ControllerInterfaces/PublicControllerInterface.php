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
 * Interface PublicControllerInterface
 * @package Interfaces\ControllerInterfaces
 */
interface PublicControllerInterface
{
    public function indexAction();

    public function loginOrRegisterAction(): void;

    public function loginAction(): void;

    public function registerAction(): void;
}