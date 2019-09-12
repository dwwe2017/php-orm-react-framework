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
use Annotations\Sidebar;
use Annotations\TopMenu;
use Controllers\PublicController;
use Controllers\RestrictedController;

/**
 * Class PublicController
 * @package Modules\Dashboard\Controllers
 * @Access(role="reseller")
 */
class IndexController extends PublicController
{
    /**
     * @Sidebar(text="Overview", title="Overview")
     * @Access(role="admin")
     */
    public function indexAction(): void
    {

    }

    /**
     * @Access(role="user")
     * @TopMenu(title="Test")
     */
    public function testAction(): void
    {

    }

    public function externalAction(): void
    {

    }
}