<?php

namespace Controllers;

use Interfaces\ControllerInterfaces\SettingsControllerInterface;

/**
 * Class SettingsFrontController
 * @package Controllers
 */
class SettingsFrontController extends SettingsController implements SettingsControllerInterface
{
    /**
     *
     */
    public function indexAction(): void
    {
        parent::indexAction(); // TODO: Change the autogenerated stub
    }
}