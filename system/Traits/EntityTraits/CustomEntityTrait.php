<?php

namespace Traits\EntityTraits;


use Exception;
use DateTime;
use Helpers\ArrayHelper;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Trait CustomEntityTrait
 * @package Traits\EntityTraits
 */
trait CustomEntityTrait
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
     * CustomEntityTrait constructor.
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        empty($data) || ArrayHelper::init($data)->mapClass($this);
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
}