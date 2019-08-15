<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Controllers;

use Interfaces\ControllerInterfaces\XmlControllerInterface;


/**
 * Class MvcController
 * @package Controllers
 */
class XmlController extends AbstractBase implements XmlControllerInterface
{
    /**
     *
     */
    public function indexAction(): void
    {

    }

    /**
     * @param string $action
     */
    public function run(string $action)
    {
        $methodName = $action . 'Action';

        if (method_exists($this, $methodName)) {
            $this->$methodName();
        } else {
            $this->render404();
        }

        $this->render();
    }

    /**
     *
     */
    public function render404(): void
    {
        header(self::HEADER_ERROR_404);
        header(self::HEADER_CONTENT_TYPE_JSON);
        $this->addContext("error", "Not Found");
        die(json_encode($this->getContext()));
    }

    /**
     *
     */
    public function render(): void
    {
        header(self::HEADER_CONTENT_TYPE_JSON);
        echo json_encode($this->getContext());
        exit();
    }
}
