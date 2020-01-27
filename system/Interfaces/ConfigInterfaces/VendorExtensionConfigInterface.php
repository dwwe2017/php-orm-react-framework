<?php

namespace Interfaces\ConfigInterfaces;


use Configs\DefaultConfig;
use Configula\ConfigValues;

/**
 * Interface VendorExtensionConfigInterface
 * @package Interfaces\ConfigInterfaces
 */
interface VendorExtensionConfigInterface
{
    public function __construct(DefaultConfig $config);

    public static function init(DefaultConfig $config): ConfigValues;

    public function getOptionsDefault(): array;
}