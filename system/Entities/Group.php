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
use Exception;
use Exceptions\InvalidArgumentException;
use Gedmo\Mapping\Annotation as Gedmo;
use Helpers\ArrayHelper;
use Interfaces\EntityInterfaces\CustomEntityInterface;


/**
 * Class User
 * @package Entities
 * @ORM\Entity
 * @ORM\Table(name="group")
 */
class Group implements CustomEntityInterface
{
    const ROLE_ROOT = 4;

    const ROLE_ADMIN = 3;

    const ROLE_RESELLER = 2;

    const ROLE_USER = 1;

    const ROLE_ANY = -1;

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", length=11, nullable=false)
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=55, nullable=false)
     */
    protected $name;

    /**
     * @var DateTime|null
     * @Gedmo\Timestampable(on="change", field={"group_id"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $changed;

    /**
     * @var DateTime|null
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updated;

    /**
     * @var DateTime|null
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $created;

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
     * CustomEntityTrait constructor.
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->users = new ArrayCollection();

        empty($data) || ArrayHelper::init($data)->mapClass($this);
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

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return DateTime|null
     * @throws Exception
     */
    public function getCreated(): ?DateTime
    {
        return $this->created ?? new DateTime();
    }

    /**
     * @return DateTime|null
     * @throws Exception
     */
    public function getUpdated(): ?DateTime
    {
        return $this->updated ?? new DateTime();
    }

    /**
     * @return DateTime|null
     * @throws Exception
     */
    public function getChanged(): ?DateTime
    {
        return $this->changed ?? new DateTime();
    }

    /**
     * @return ArrayCollection|null
     */
    public function getUsers(): ?ArrayCollection
    {
        return $this->users;
    }
}
