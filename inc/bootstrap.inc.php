<?php

use Handlers\ErrorHandler;

/**
 * @var $autoload Composer\Autoload\ClassLoader
 */
$autoload = require_once __DIR__ . '/../autoload.php';

/**
 * Without transferring the Config, the error handling itself attempts to obtain the config
 * and sets the debug mode to true in the event of an error, whereby all errors are output in a readable manner.
 */
ErrorHandler::init();