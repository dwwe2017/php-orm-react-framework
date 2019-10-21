<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Entities;

use Doctrine\Common\Collections\ArrayCollection;
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
    protected $by_id;

    /**
     * @var int|null
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    protected $group_id;

    /**
     * @var string
     * @ORM\Column(type="string", length=55, nullable=false, options={"default"=""})
     */
    protected $password = "";

    /**
     * @var Group|null
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="users")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    protected $group;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="User", inversedBy="users")
     * @ORM\JoinColumn(name="by_id", referencedColumnName="id")
     */
    protected $by;

    /**
     * @var
     * @ORM\OneToMany(targetEntity="User", mappedBy="by")
     * @ORM\JoinColumn(name="id", referencedColumnName="by_id")
     */
    protected $users;

    /**
     *
     */
    protected function init()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * @param string $password
     * @return bool
     */
    public function isValidPassword(string $password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * @param Group|null $group
     */
    public function setGroup(?Group $group = null): void
    {
        $this->group = $group;
    }

    /**
     * @return Group|null
     */
    public function getGroup(): ?Group
    {
        return $this->group;
    }

    /**
     * @return User|null
     */
    public function getBy(): ?User
    {
        return $this->by;
    }

    /**
     * @param User|null $by
     */
    public function setBy(?User $by = null): void
    {
        $this->by = $by;
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }
}