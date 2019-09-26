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
 * Class PublicController
 * @package Controllers
 */
class PublicXmlController extends PublicController implements XmlControllerInterface
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
    public final function run(string $action)
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
    public final function render(): void
    {
        header(self::HEADER_CONTENT_TYPE_JSON);
        echo json_encode($this->getContext());
        exit();
    }
}
