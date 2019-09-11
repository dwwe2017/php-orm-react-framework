<?php

namespace Traits\EntityTraits;


use Helpers\ArrayHelper;

/**
 * Trait CustomEntityTrait
 * @package Traits\EntityTraits
 */
trait CustomEntityTrait
{
    /**
     * CustomEntityTrait constructor.
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        empty($data) || ArrayHelper::init($data)->mapClass($this);
    }
}