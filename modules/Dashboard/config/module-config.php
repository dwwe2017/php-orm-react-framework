<?php
/**
 * Module Configuration
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

$config = [
    "debug_mode" => false,
    "logger_options" => [
        "log_level" => \Monolog\Logger::EMERGENCY
    ],
];