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
 * Interface RestrictedControllerInterface
 * @package Interfaces\ControllerInterfaces
 */
interface RestrictedControllerInterface
{
    public function indexAction(): void;

    public function loginOrRegisterAction(): void;

    public function loginAction(): void;

    public function registerAction(): void;
}