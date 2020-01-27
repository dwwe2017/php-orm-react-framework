<?php

namespace Interfaces\ConfigInterfaces;


use Configs\DefaultConfig;
use Managers\ModuleManager;

/**
 * Interface ApplicationConfigInterface
 * @package Interfaces\ConfigInterfaces
 */
interface ApplicationConfigInterface
{
    public function __construct(ModuleManager $moduleManager);

    public static function init(ModuleManager $moduleManager): DefaultConfig;

    public function getOptionsDefault(): array;
}