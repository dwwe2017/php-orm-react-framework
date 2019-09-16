<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Modules\Dashboard\Controllers;


use Annotations\Access;
use Annotations\Info;
use Annotations\Navigation;
use Annotations\SubNavigation;
use Controllers\RestrictedController;

/**
 * Class PublicController
 * @package Modules\Dashboard\Controllers
 * @Access(role="user")
 * @Navigation(position="sidebar")
 * @Info(author="DW </> Web-Engineering", website="https://dwwe.de", email="daniel@dwwe.de")
 */
class IndexController extends RestrictedController
{
    /**
     * @Access(role="admin")
     * @SubNavigation(text="Overview")
     */
    public function indexAction(): void
    {

    }
}