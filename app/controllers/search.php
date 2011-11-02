<?php

/**
 * Make sure everything else been initialized first.
 */
if (class_exists('initialize') === FALSE) {
    exit;
}

/**
 * The search controller
 * Works out if the search query is valid (non-empty),
 * and if so, gets the search-model to do the search and process it.
 */
class search extends initialize
{

    /**
     * Works out if the search query is valid (non-empty),
     * and if so, gets the search-model to do the search and process it.
     *
     * @param $info mixed The search info (get query, page number etc).
     *
     * @return void
     */
    public static function process($info=NULL)
    {
        if ($info === NULL || empty($info) === TRUE) {
            template::printTemplate(__CLASS__, 'searchform');
            return;
        }
    }
}

/* vim: set expandtab ts=4 sw=4: */

