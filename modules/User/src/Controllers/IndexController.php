<?php


namespace Modules\User\Controllers;


use Annotations\Access;
use Annotations\Navigation;
use Annotations\SubNavigation;
use Controllers\PublicController;
use Entities\User;
use Exceptions\DoctrineException;
use Helpers\ViewHelper;

/**
 * Class IndexController
 * @package Modules\User\Controllers
 * @Access(role="admin")
 * @Navigation(position="sidebar", icon="icon-group", text="Manage users")
 */
class IndexController extends PublicController
{
    /**
     * @throws DoctrineException
     */
    public function indexAction(): void
    {
        $this->usersAction();
    }

    /**
     * @SubNavigation(text="All users")
     * @throws DoctrineException
     */
    public function usersAction(): void
    {
        $em = $this->getModuleDbService()->getEntityManager();
        $data = ViewHelper::getResponsiveTableArrayFromEntity($em,
            User::class,
            "icon-reorder",
            true,
            true,
            ["user", "index", "user"],
            ["user", "index", "userDel"]
        );

        $this->addContext("data", $data);
    }

    /**
     * @SubNavigation(text="Search user", icon="icon-search")
     */
    public function userSearchAction(): void
    {

    }

    /**
     * @SubNavigation(text="Add user", icon="icon-plus")
     */
    public function userAddAction(): void
    {

    }

    /**
     *
     */
    public function userAction(): void
    {

    }
}