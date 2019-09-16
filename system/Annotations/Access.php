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
     * @Enum({"root", "admin", "reseller", "user"})
     */
    public $role;
}