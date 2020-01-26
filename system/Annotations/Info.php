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
    public string $author;

    /**
     * @var string
     */
    public string $email;

    /**
     * @var string
     */
    public string $website;

    /**
     * @var string
     */
    public string $description;
}