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
     * @SubNavigation(text="All users")
     * @throws DoctrineException
     */
    public function indexAction(): void
    {
        $em = $this->getModuleDbService()->getEntityManager();
        $viewHelper = EntityViewHelper::init($em);

        $data = $viewHelper->getResponsiveTableArrayFromEntity(
            User::class,
            $this->getSessionHandler()->getUsers(),
            "icon-reorder",
            true, true,
            ["user", "index", "userAdd"],
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