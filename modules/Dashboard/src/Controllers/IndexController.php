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
use Annotations\Navigation;
use Annotations\SubNavigation;
use Controllers\RestrictedController;

/**
 * Class PublicController
 * @package Modules\Dashboard\Controllers
 * @Access(role=Entities\Group::ROLE_USER)
 * @Navigation(position="sidebar", icon="icon-dashboard")
 */
class IndexController extends RestrictedController
{
    /**
     * @SubNavigation(text="Overview")
     */
    public function indexAction(): void
    {

    }
}