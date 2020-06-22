<?php
/**
 * MIT License
 *
 * Copyright (c) 2020 DW Web-Engineering
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Entities;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Exceptions\InvalidArgumentException;
use Gedmo\Mapping\Annotation as Gedmo;
use Helpers\ArrayHelper;


/**
 * Class User
 * @package Entities
 * @ORM\Entity
 * @ORM\Table(name="group")
 */
class Group
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
    protected int $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=55, nullable=false, options={"default"=""})
     */
    protected string $name = "";

    /**
     * @var DateTime|null
     * @Gedmo\Timestampable(on="change", field={"group_id"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $changed;

    /**
     * @var DateTime|null
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $updated;

    /**
     * @var DateTime|null
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $created;

    /**
     * @var int
     * @ORM\Column(type="integer", length=2, nullable=false, options={"default"=-1})
     */
    protected int $role = self::ROLE_ANY;

    /**
     * @var
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
