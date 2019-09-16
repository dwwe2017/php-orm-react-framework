<?php


namespace Modules\Dashboard\Controllers;


use Annotations\Access;
use Controllers\RestrictedXmlController;

class AjaxController extends RestrictedXmlController
{
    /**
     * @Access(role="root")
     */
    public function indexAction(): void
    {
        $this->addContext("test", "test");
    }
}