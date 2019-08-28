<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

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
    private static $instanceKey = "";
}