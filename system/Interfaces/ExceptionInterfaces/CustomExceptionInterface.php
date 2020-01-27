<?php

namespace Interfaces\ExceptionInterfaces;


use Throwable;

/**
 * Interface CustomExceptionInterface
 * @package Interfaces\ExceptionInterfaces
 */
interface CustomExceptionInterface
{
    public function __construct($message = "", $code = 0, Throwable $previous = null);
}