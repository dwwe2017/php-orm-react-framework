<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Modules\Dashboard\Controllers;


use Controllers\PublicController;

/**
 * Class PublicController
 * @package Modules\Dashboard\Controllers
 */
class IndexController extends PublicController
{
    /**
     *
     */
    public function indexAction(): void
    {
        $em = $this->getModuleDbService()->getEntityManager();
    }
}