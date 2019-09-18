<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Interfaces\EntityInterfaces\CustomEntityInterface;
use Traits\EntityTraits\CustomEntityTrait;


/**
 * Class User
 * @package Entities
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User implements CustomEntityInterface
{
    use CustomEntityTrait;

    /**
     * @var int|null
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    protected $group_id;
}