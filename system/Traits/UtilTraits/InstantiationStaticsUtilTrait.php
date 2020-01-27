<?php

namespace Traits\UtilTraits;


/**
 * Trait InstantiationStaticsUtilTrait
 * @package Traits\UtilTraits
 */
trait InstantiationStaticsUtilTrait
{
    /**
     * @var self|null
     */
    private static $instance = null;

    /**
     * @var string
     */
    private static string $instanceKey = "";
}