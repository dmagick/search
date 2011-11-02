<?php

/**
 * This is an abstract class so the other controllers can extend it and use
 * the initialize method, but also do their own thing.
 */
abstract class initialize
{
    protected static $_basedir = NULL;

    /**
     * This is a static class. Calling it with 'new initisalize' should fail.
     */
    public function __construct()
    {
        return FALSE;
    }

    /**
     * This sets up everything for us.
     * Loads other systems, includes the config, gets everything ready to go.
     *
     *
     * @param string $basedir The base directory of the app.
     *
     * @return boolean
     */
    public static function initialize($basedir=NULL)
    {
        if ($basedir === NULL) {
            return FALSE;
        }

        /**
         * This is a bit of a silly check, but lets make sure it's a valid base.
         * We know where *we* should be - let's check.
         */
        if (is_file($basedir.'/app/controllers/initialize.php') === FALSE) {
            return FALSE;
        }

        /**
         * Passed that check - phew! We're good to go.
         */
        self::$_basedir = $basedir;

        return TRUE;
    }

    public static function process()
    {
        /**
         * We're always going to need the template controller.
         * Let's include it now.
         */
        require self::$_basedir.'/app/controllers/templates.php';

        templates::initialize(self::$_basedir);

        $configFile = self::$_basedir.'/app/config.php';
        if (is_file($configFile) === FALSE) {
            templates::printTemplate(NULL, 'configuration_required', 500);
            exit;
        }

        $config = require $configFile;
        if (isset($config['flickrApiKey']) === FALSE || empty($config['flickrApiKey']) === TRUE) {
            templates::printTemplate(NULL, 'configuration_required', 500);
            exit;
        }

        if (is_dir(self::$_basedir.'/cache') === FALSE) {
            templates::printTemplate(NULL, 'configuration_required', 500);
            exit;
        }
        if (is_writable(self::$_basedir.'/cache') === FALSE) {
            templates::printTemplate(NULL, 'configuration_required', 500);
            exit;
        }
    }
}

function error_handler($errno, $errstr, $errfile, $errline)
{
    switch ($errno)
    {
        case E_USER_ERROR:
            error_log("Got error ${errstr} (${errno}) from ${errfile} on line ${errline}");
            exit;
        break;
        default:
            error_log("Got error ${errstr} (${errno}) from ${errfile} on line ${errline}");
    }
    //return TRUE;
}

set_error_handler('error_handler');

/* vim: set expandtab ts=4 sw=4: */

