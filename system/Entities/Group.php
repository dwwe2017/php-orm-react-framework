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

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", length=11, nullable=false)
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=55, nullable=false, options={"default": ""})
     */
    protected $name = "";

    /**
     * @var \DateTime|null
     * @Gedmo\Timestampable(on="change", field={"name"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $changed;

    /**
     * @var \DateTime|null
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updated;

    /**
     * @var \DateTime|null
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $created;

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
     * @return \DateTime|null
     */
    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdated(): ?\DateTime
    {
        return $this->updated;
    }
}