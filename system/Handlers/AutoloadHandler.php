<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Handlers;


use Composer\Autoload\ClassLoader;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class AutoloadHandler
 * @package Handlers
 */
class AutoloadHandler
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var ClassLoader
     */
    private $classLoader;

    /**
     * AutoloadHandler constructor.
     * @param ClassLoader $classLoader
     * @param string $baseDir
     */
    public final function __construct(string $baseDir, ClassLoader $classLoader)
    {
        $this->classLoader = $classLoader;

        $modulesDir = sprintf("%s/modules", $baseDir);

        foreach (scandir($modulesDir) as $item) {
            $path = sprintf("%s/%s", $modulesDir, $item);
            if ($item == "." || $item == ".." || is_file($path)) {
                continue;
            }

            $namespace = sprintf("Modules\\%s\\", ucfirst($item));
            $classpath = sprintf("%s/src", $path);

            $this->classLoader->addPsr4($namespace, $classpath);
        }
    }

    /**
     * @param string $baseDir
     * @param ClassLoader $classLoader
     * @return AutoloadHandler|null
     */
    public static final function init(string $baseDir, ClassLoader $classLoader)
    {
        if (is_null(self::$instance) || serialize($baseDir).serialize($classLoader) !== self::$instanceKey) {
            self::$instance = new self($baseDir, $classLoader);
            self::$instanceKey = serialize($baseDir).serialize($classLoader);
        }

        return self::$instance;
    }
}