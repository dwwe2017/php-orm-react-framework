<?php


namespace Annotations;


/**
 * Class Access
 * @package Annotations
 * @Annotation
 * @Target({"METHOD", "CLASS"})
 */
class TopMenu
{
    /**
     * @var string
     */
    public $text;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $alt;

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
    public $class;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $externalLink;

    /**
     * @var string
     * @Enum({"_blank", "_self", "_parent"})
     */
    public $target;
}