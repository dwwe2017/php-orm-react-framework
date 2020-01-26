<?php


namespace Annotations;


use Doctrine\Common\Annotations\Annotation\Enum;

/**
 * Class Access
 * @package Annotations
 * @Annotation
 * @Target({"METHOD", "CLASS"})
 */
class Access
{
    /**
     * @Enum({Entities\Group::ROLE_ROOT, Entities\Group::ROLE_ADMIN, Entities\Group::ROLE_RESELLER, Entities\Group::ROLE_USER})
     */
    public string $role;
}