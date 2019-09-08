<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Traits\EntityTraits;


use Helpers\ArrayHelper;

/**
 * Trait ConstructorMappingEntityTrait
 * @package Traits\EntityTraits
 */
trait ConstructorMappingEntityTrait
{
    /**
     * ConstructorMappingEntityTrait constructor.
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        ArrayHelper::init($data)->mapClass($this);
    }
}