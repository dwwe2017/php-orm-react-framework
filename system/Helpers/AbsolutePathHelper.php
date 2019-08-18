<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Helpers;


use Exceptions\FileFactoryException;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class AbsolutePathHelper
 * @package Helpers
 */
class AbsolutePathHelper
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var string
     */
    private $baseDir = "";

    /**
     * AbsolutePathHelper constructor.
     * @param string $baseDir
     */
    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;
    }

    /**
     * @param string $baseDir
     * @return AbsolutePathHelper|null
     */
    public static function init(string $baseDir)
    {
        if (is_null(self::$instance) || serialize($baseDir) !== self::$instanceKey) {
            self::$instance = new self($baseDir);
            self::$instanceKey = serialize($baseDir);
        }

        return self::$instance;
    }

    /**
     * @param string $relativePath
     * @return string
     * @throws FileFactoryException
     */
    public function get(string $relativePath)
    {
        $absolutePath = sprintf("%s/%s", $this->getBaseDir(), $relativePath);

        if (!file_exists($absolutePath) || !is_readable($absolutePath)) {
            throw new FileFactoryException(sprintf("The file %s could not be found or can not be loaded", $absolutePath));
        }

        return sprintf("%s/%s", $this->getBaseDir(), $relativePath);
    }

    /**
     * Magic method (eg. AbsolutePathHelper($baseDir)->{"relative/path/example"}
     * ==> /var/www/htdocs/relative/path/example)
     *
     * @param string $relativePath
     * @return string
     * @throws FileFactoryException
     */
    public function __get(string $relativePath)
    {
        return $this->get($relativePath);
    }

    /**
     * @return string
     */
    public function getBaseDir(): string
    {
        return $this->baseDir;
    }
}