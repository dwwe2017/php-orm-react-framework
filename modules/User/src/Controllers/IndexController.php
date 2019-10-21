<?php


namespace Modules\User\Controllers;


use Annotations\Access;
use Annotations\Navigation;
use Annotations\SubNavigation;
use Controllers\RestrictedController;
use Entities\User;
use Exceptions\DoctrineException;
use Helpers\EntityViewHelper;

/**
 * Class IndexController
 * @package Modules\User\Controllers
 * @Access(role=Entities\Group::ROLE_RESELLER)
 * @Navigation(position="sidebar", icon="icon-group", text="Manage users")
 */
class IndexController extends RestrictedController
{
    /**
     * @internal Dispatch
     */
    public function indexAction(): void
    {
        $this->redirect("user", "index", "list");
    }

    /**
     * @SubNavigation(text="All users")
     * @internal ReactJS
     * @see ApiController::listAction()
     */
    public function listAction(): void
    {

    }

    /**
     * @SubNavigation(text="Add user", icon="icon-plus")
     */
    public function userAddAction(): void
    {

    }
}