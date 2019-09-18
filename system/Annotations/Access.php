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
     *
     */
    const ROOT = 4;

    /**
     *
     */
    const ADMIN = 3;

    /**
     *
     */
    const RESELLER = 2;

    /**
     *
     */
    const USER = 1;

    /**
     *
     */
    const ANY = -1;

    /**
     * @Enum({"root", "admin", "reseller", "user"})
     */
    public $role;
}