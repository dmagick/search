<?php

/**
 * This is an abstract class so the other controllers can extend it and use
 * the initialize method, but also do their own thing.
 */
abstract class initialize
{
    /**
     * Keep a reference to the base directory so we know where to start
     * looking for things.
     */
    protected static $_basedir = NULL;

    /**
     * Keep a reference to the config so we only load it up once.
     * This is passed to all models - they can deal with it as they wish.
     */
    protected static $_config = array();

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
     * This checks the config is set up, logs folder exists and is writable
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
        require self::$_basedir.'/app/controllers/template.php';

        template::initialize(self::$_basedir);

        $configFile = self::$_basedir.'/app/config.php';
        if (is_file($configFile) === FALSE) {
            template::printTemplate(NULL, 'configuration_required', 500);
            exit;
        }

        $errors = array();
        $config = require $configFile;
        if (isset($config['flickrApiKey']) === FALSE || empty($config['flickrApiKey']) === TRUE) {
            $errors[] = array('error' => 'Please set the flickr api key in app/config.php.');
        }

        $logDir = self::$_basedir.'/app/logs';

        if (is_dir($logDir) === FALSE) {
            $errors[] = array('error' => 'Please create an app/logs directory and make sure it\'s writable by the web server.');
        } else {
            if (is_writable($logDir) === FALSE) {
                $errors[] = array('error' => 'The app/logs directory exists but it\'s not writable by the web server.');
            }
        }

        if (empty($errors) === FALSE) {
            template::setKeyword('configuration_required:errors:errors', $errors);
            template::printTemplate(NULL, 'configuration_required', 500);
            exit;
        }

        self::$_config = $config;

        $controller = 'search';
        $otherInfo  = '';
        $queryInfo  = $_GET;

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
            template::printTemplate(NULL, '404', 404);
            exit;
        }

        require $controllerFile;
        $controller::process($otherInfo, $queryInfo);
    }

    /**
     * Get a model for a controller to work with.
     * If it can't be found, returns false.
     * Otherwise returns the model ready for use.
     *
     * @param string $modelName The model to get.
     *
     * @return mixed
     */
    protected static function getModel($modelName=NULL)
    {
        if ($modelName === NULL) {
            return FALSE;
        }

        $modelFile = self::$_basedir.'/app/models/'.$modelName.'.php';
        if (file_exists($modelFile) === FALSE) {
            return FALSE;
        }

        require $modelFile;
        $modelName = $modelName.'Model';
        $model     = $modelName::getInstance(self::$_config);
        return $model;
    }

    /**
     * Print the generic server error page ('uhoh'), with the appropriate header.
     *
     * @return void
     */
    protected static function printServerError()
    {
            header('HTTP/1.1 500 Internal Server Error');
            echo template::printTemplate(NULL, 'uhoh');
    }

    /**
     * Error handler.
     * This logs the error and where it comes from to the app/logs/errors.log file.
     * If it's a E_USER_ERROR, it also displays the server error page to let
     * the user know something went really wrong.
     */
    public static function error_handler($errno, $errstr, $errfile, $errline)
    {
        $message  = date('r')."\tGot error ${errstr} (${errno}) from ";
        $message .= "${errfile} on line ${errline}\n";
        error_log($message, 3, self::$_basedir.'/app/logs/errors.log');

        // Leave this as a switch in case we need to extend it later.
        switch ($errno)
        {
            case E_USER_ERROR:
                self::printServerError();
                exit;
            break;
        }
        return TRUE;
    }

}

set_error_handler(array('initialize', 'error_handler'));

/* vim: set expandtab ts=4 sw=4: */

