<?php


namespace Modules\User\Controllers;


use Controllers\RestrictedXmlController;

/**
 * Class ApiController
 * @package Modules\User\Controllers
 */
class ApiController extends RestrictedXmlController
{
    /**
     *
     */
    public function listAction()
    {
        $this->addContext("data", $this->getSessionHandler()->getUsersArray());
    }
}