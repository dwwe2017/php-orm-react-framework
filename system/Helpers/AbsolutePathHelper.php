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
    private string $baseDir = "";

    /**
     * AbsolutePathHelper constructor.
     * @param string $baseDir
     */
    public final function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;
    }

    /**
     * @param string $baseDir
     * @return AbsolutePathHelper|null
     */
    public static final function init(string $baseDir)
    {
        if (is_null(self::$instance) || serialize($baseDir) !== self::$instanceKey) {
            self::$instance = new self($baseDir);
            self::$instanceKey = serialize($baseDir);
        }

        return self::$instance;
    }

    /**
     * @param string $relativePath
     * @param bool $throw_e
     * @return string
     */
    public final function get(string $relativePath, $throw_e = true)
    {
        $absolutePath = sprintf("%s/%s", $this->getBaseDir(), $relativePath);
        FileHelper::init($absolutePath, $throw_e ? FileFactoryException::class : null)->isReadable();

        return sprintf("%s/%s", $this->getBaseDir(), $relativePath);
    }

    /**
     * @return string
     */
    public final function getBaseDir(): string
    {
        return $this->baseDir;
    }
}