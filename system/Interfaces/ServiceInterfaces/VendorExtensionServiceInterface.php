<?php

namespace Interfaces\ServiceInterfaces;


use Managers\ModuleManager;

/**
 * Interface VendorExtensionServiceInterface
 * @package Interfaces\ServiceInterfaces
 */
interface VendorExtensionServiceInterface
{
    public function __construct(ModuleManager $moduleManager);

    public static function init(ModuleManager $moduleManager);
}