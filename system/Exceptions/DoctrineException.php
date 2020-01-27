<?php

namespace Exceptions;

use Exception;
use Interfaces\ExceptionInterfaces\CustomExceptionInterface;
use Throwable;

class DoctrineException extends Exception implements CustomExceptionInterface
{
    /**
     * DoctrineException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public final function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = is_array($message) ? json_encode($message) : $message;

        parent::__construct($message, $code, $previous);
    }
}