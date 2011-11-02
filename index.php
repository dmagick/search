<?php

/**
 * Work out where we are. This will tell the init controller
 * where to start looking for things.
 */
$basedir = dirname(__FILE__);

require $basedir.'/app/controllers/initialize.php';
initialize::initialize($basedir);

initialize::process();

/* vim: set expandtab ts=4 sw=4: */

