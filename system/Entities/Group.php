<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Interfaces\EntityInterfaces\CustomEntityInterface;
use Traits\EntityTraits\CustomEntityTrait;


/**
 * Class User
 * @package Entities
 * @ORM\Entity
 * @ORM\Table(name="group")
 */
class Group implements CustomEntityInterface
{
    use CustomEntityTrait;
}