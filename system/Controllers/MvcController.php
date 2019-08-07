<?php

namespace Controllers;

use Interfaces\ControllerInterfaces\MvcControllerInterface;
use Modules\Dashboard\Controllers\IndexController;

/**
 * Class MvcController
 * @package Controllers
 */
class MvcController extends AbstractBase implements MvcControllerInterface
{
    /**
     *
     */
    public function indexAction(): void
    {
        if (class_exists(IndexController::class)) {
            $this->redirect("dashboard", "index", "index");
        }
    }
}
