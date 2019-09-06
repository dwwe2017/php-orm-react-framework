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
use Helpers\ArrayHelper;


/**
 * Class User
 * @package Entities
 * @ORM\Table(name="user")
 */
class User
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", length=11, nullable=false)
     */
    public $id;

    /**
     * @var int|null
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    public $group_id;

    /**
     * User constructor.
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        ArrayHelper::init($data)->mapClass($this);
    }

    /**
     * @return int|null
     */
    public function getGroupId(): ?int
    {
        return $this->group_id;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}