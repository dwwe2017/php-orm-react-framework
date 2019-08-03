<?php

use Handlers\ErrorHandler;


require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Without transferring the CoreConfig, the error handling itself attempts to obtain the config
 * and sets the debug mode to true in the event of an error, whereby all errors are output in a readable manner.
 */
ErrorHandler::init();