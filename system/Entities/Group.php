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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Exceptions\InvalidArgumentException;
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

    const ROLE_ROOT = 4;

    const ROLE_ADMIN = 3;

    const ROLE_RESELLER = 2;

    const ROLE_USER = 1;

    const ROLE_ANY = -1;

    /**
     * @var int
     * @ORM\Column(type="integer", length=2, nullable=false, options={"default"=-1})
     */
    protected $role = self::ROLE_ANY;

    /**
     * @var ArrayCollection|null
     * @ORM\OneToMany(targetEntity="User", mappedBy="group")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    protected $users;

    /**
     *
     */
    public function init(): void
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getRole(): int
    {
        return $this->role;
    }

    /**
     * @param int $role
     * @throws InvalidArgumentException
     */
    public function setRole(int $role): void
    {
        if (!in_array($role, [self::ROLE_ROOT, self::ROLE_ADMIN, self::ROLE_RESELLER, self::ROLE_USER, self::ROLE_ANY])) {
            throw new InvalidArgumentException("Invalid role");
        }

        $this->role = $role;
    }
}