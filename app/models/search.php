<?php

/**
 * The search model.
 * This talks to the flickr API to do all of it's work.
 * It just does the searching and returning of results.
 */
class searchModel
{

    /**
     * So we only load ourselves once, keep a note here.
     */
    private static $_instance = NULL;

    /**
     * Keep a reference to the config so we only load it up once.
     * This is passed to all models - they can deal with it as they wish.
     */
    protected static $_config = array();

    private function __construct()
    {
    }

    public function getInstance($config=array())
    {
        if (self::$_instance === NULL) {
            $class = __CLASS__;
            self::$_instance = new $class;
            self::$_instance->config = $config;
        }
        return self::$_instance;
    }

    /**
     * This does the searching, and returns the results.
     * If it can't connect to the flickr site for whatever reason,
     * it throws an exception.
     * If it can't search for whatever reason, it throws an exception.
     * If everything goes ok, then it simply returns the results - whatever
     * they may be.
     * It's up to the controller or function calling this to deal with things.
     */
    public static function search($searchTerms=array(), $page=-1)
    {

        if (empty($searchTerms) === TRUE) {
            return FALSE;
        }

        if (is_array($searchTerms) === FALSE) {
            $searchTerms = explode(' ', $searchTerms);
        }

        /**
         * Make sure the page we've been given is a number and is valid (> 0).
         */
        $pageNum = 0;
        if ($page == intval($page)) {
            $pageNum = intval($page);
        }
        if ($pageNum < 1) {
            $pageNum = 1;
        }
    }
}

/* vim: set expandtab ts=4 sw=4: */

