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
    public $position;

    /**
     * @var string
     */
    public $text;

    /**
     * @var string
     */
    public $icon = "icon-angle-right";

    /**
     * @var string
     */
    public $title;

    /**
     * @var bool
     */
    public $hidden;

    /**
     * @var string
     */
    public $style;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $href = "javascript:void(0)";

    /**
     * @var string
     * @Enum({"_blank", "_self", "_parent"})
     */
    public $target;
}