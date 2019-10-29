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
use Gedmo\Mapping\Annotation as Gedmo;
use Helpers\ArrayHelper;
use Interfaces\EntityInterfaces\CustomEntityInterface;


/**
 * Class User
 * @package Entities
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User implements CustomEntityInterface
{
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
     * @var string
     * @ORM\Column(type="string", length=5, nullable=false, options={"default"="en_US"})
     */
    protected $locale = "en_US";

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
     * CustomEntityTrait constructor.
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->users = new ArrayCollection();

        empty($data) || ArrayHelper::init($data)->mapClass($this);
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

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
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
}
