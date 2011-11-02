<?php

/**
 * Make sure everything else been initialized first.
 */
if (class_exists('initialize') === FALSE) {
    exit;
}

class templates extends initialize
{
    /**
     * Where to start looking for templates.
     */
    private static $_templateDir = NULL;

    /**
     * So we only have to read things once per request,
     * lets keep the original template cached.
     */
    private static $_templateCache = array();

    /**
     * Our own initialize method. Check with the parent that it's ok to proceed first.
     * Then we set our templateDir appropriately.
     *
     * @param string $basedir The base directory of the app.
     *
     * @return boolean
     */
    public static function initialize($basedir=NULL)
    {
        if (parent::initialize($basedir) === FALSE) {
            return FALSE;
        }

        self::$_templateDir = $basedir.'/app/views';

        return TRUE;
    }

    /**
     * Prints a template out, doesn't return anything.
     *
     * @param string  $controller   Which controller is displaying the template.
     * @param string  $templateName Template name to print out.
     * @param integer $httpCode     The http header code to use. Currently only supports 200 and 404.
     *
     * @return void
     */
    public static function printTemplate($controller=NULL, $templateName=NULL, $httpCode=200)
    {
        if ($controller === NULL) {
            $template = self::$_templateDir.'/'.$templateName.'.html';
        } else {
            $template = self::$_templateDir.'/'.$controller.'/'.$templateName.'.html';
        }
        if (file_exists($template) === FALSE) {
            header('HTTP/1.1 500 Internal Server Error');
            echo self::_process(self::$_templateDir.'/header.html');
            echo self::_process(self::$_templateDir.'/uhoh.html');
            echo self::_process(self::$_templateDir.'/footer.html');
            trigger_error('Template '.$templateName.' doesn\'t exist', E_USER_ERROR);
            exit;
        }

        switch ($httpCode) {
            case 404:
                header('Status: 404 Not Found');
            break;
            default:
        }

        echo self::_process($template);
    }

    /**
     * Processes the template for keywords (including base keywords, includes etc)
     * and returns the new contents of the template.
     *
     * @param string $template The full path to the template to process.
     *                         It has already been checked to make sure it exists, so
     *                         we don't need to do it again.
     *
     * @return string
     */
    private static function _process($template)
    {
        if (isset(self::$_templateCache[$template]) === FALSE) {
            $contents = file_get_contents($template);
            self::$_templateCache[$template] = $contents;
        } else {
            $contents = self::$_templateCache[$template];
        }

        preg_match_all('/~template::include:(.*?)~/', $contents, $matches);
        if (empty($matches[0]) === FALSE) {
            foreach ($matches[0] as $match => $includeTemplate) {
                $contents = str_replace($includeTemplate, self::_process(self::$_templateDir.'/'.$matches[1][$match]), $contents);
            }
        }
        return $contents;
    }
}

/* vim: set expandtab ts=4 sw=4: */

