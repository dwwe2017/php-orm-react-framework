<?php


namespace Annotations;


use Doctrine\Common\Annotations\Annotation\Enum;

/**
 * Class Access
 * @package Annotations
 * @Annotation
 * @Target("CLASS")
 */
class Navigation
{
    /**
     * @var string
     * @Enum({"sidebar", "top_left", "top_right", "misc"})
     */
    public string $position;

    /**
     * @var string
     */
    public string $text;

    /**
     * @var string
     */
    public string $class;

    /**
     * @var string
     */
    public string $icon = "icon-angle-right";

    /**
     * @var string
     */
    public string $title;

    /**
     * @var bool
     */
    public bool $hidden;

    /**
     * @var string
     */
    public string $style;

    /**
     * @var string
     */
    public string $description;

    /**
     * @var string
     */
    public string $href = "javascript:void(0)";

    /**
     * @var string
     * @Enum({"_blank", "_self", "_parent"})
     */
    public string $target;
}