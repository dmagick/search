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

    /**
     * All the "magic" works here.
     * This checks the config is set up, cache folder exists and is writable
     * and if neither of those are true, then displays an appropriate message.
     *
     * If it is true, it works out the url you're trying to view and passes it to the
     * appropriate controller to deal with.
     *
     * @return void
     */
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

        $controller = 'search';
        $otherInfo  = '';
        /**
         * If we've got a url, lets see if it's valid.
         * /controller/stuff
         * stuff is passed to the controller::process() method.
         */
        if (isset($_SERVER['PATH_INFO']) === TRUE) {
            $info       = trim($_SERVER['PATH_INFO'], '/');
            $bits       = explode('/', $info);
            $controller = array_shift($bits);
            $otherInfo  = implode('/', $bits);
        }

        /**
         * Allow access to only particular controllers:
         * - search
         * mainly so people can't guess this class name and cause errors
         * and same for templates.
         */
        $allowedControllers = array(
                               'search',
                              );

        $controllerFile = self::$_basedir.'/app/controllers/'.$controller.'.php';

        if (file_exists($controllerFile) === FALSE || in_array($controller, $allowedControllers) === FALSE) {
            templates::printTemplate(NULL, '404', 404);
            exit;
        }

        require $controllerFile;
        $controller::process($otherInfo);
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

