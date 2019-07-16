<?php

// Doctrine CLI configuration file

use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once dirname(__DIR__) . '/inc/bootstrap.inc.php';

return ConsoleRunner::createHelperSet($em);
