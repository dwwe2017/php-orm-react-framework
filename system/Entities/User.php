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
use Gedmo\Mapping\Annotation as Gedmo;
use Helpers\ArrayHelper;


/**
 * Class User
 * @package Entities
 * @ORM\Entity
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
    protected int $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=55, nullable=false, options={"default"=""})
     */
    protected string $name = "";

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false, options={"default"="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAIAAABuYg/PAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyxpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTIzIDc5LjE1ODk3OCwgMjAxNi8wMi8xMy0wMToxMToxOSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIEVsZW1lbnRzIDE1LjAgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjQwNjBGNzMyMzBERjExRUE5NDE5RDZDOTExNzhGMzNBIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjQwNjBGNzMzMzBERjExRUE5NDE5RDZDOTExNzhGMzNBIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NDA2MEY3MzAzMERGMTFFQTk0MTlENkM5MTE3OEYzM0EiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6NDA2MEY3MzEzMERGMTFFQTk0MTlENkM5MTE3OEYzM0EiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7pPZXfAAAIvklE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"})
     */
    protected string $avatar = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAIAAABuYg/PAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyxpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTIzIDc5LjE1ODk3OCwgMjAxNi8wMi8xMy0wMToxMToxOSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIEVsZW1lbnRzIDE1LjAgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjQwNjBGNzMyMzBERjExRUE5NDE5RDZDOTExNzhGMzNBIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjQwNjBGNzMzMzBERjExRUE5NDE5RDZDOTExNzhGMzNBIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NDA2MEY3MzAzMERGMTFFQTk0MTlENkM5MTE3OEYzM0EiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6NDA2MEY3MzEzMERGMTFFQTk0MTlENkM5MTE3OEYzM0EiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7pPZXfAAAIvklE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";

    /**
     * @var int|null
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    protected ?int $by_id;

    /**
     * @var int|null
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    protected ?int $group_id;

    /**
     * @var string
     * @ORM\Column(type="string", length=55, nullable=false, options={"default"=""})
     */
    protected string $password = "";

    /**
     * @var string
     * @ORM\Column(type="string", length=5, nullable=false, options={"default"="en_US"})
     */
    protected string $locale = "en_US";

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
     * @var Group|null
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="users")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    protected ?Group $group;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="User", inversedBy="users")
     * @ORM\JoinColumn(name="by_id", referencedColumnName="id")
     */
    protected ?User $by;

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

    /**
     * @return string
     */
    public function getAvatar(): string
    {
        return $this->avatar;
    }
}
