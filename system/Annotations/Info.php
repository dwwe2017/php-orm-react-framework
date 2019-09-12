<?php


namespace Annotations;


/**
 * Class Access
 * @package Annotations
 * @Annotation
 * @Target({"METHOD", "CLASS"})
 */
class Info
{
    /**
     * @var string
     */
    public $author;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $website;

    /**
     * @var string
     */
    public $description;
}