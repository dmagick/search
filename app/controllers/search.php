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
    public static function process($info=NULL, $queryInfo=array())
    {
        if (($info === NULL || empty($info) === TRUE) && empty($queryInfo) === TRUE) {
            template::printTemplate(__CLASS__, 'searchform');
            return;
        }

        if (empty($queryInfo) === TRUE || isset($queryInfo['search']) === FALSE) {
            template::printTemplate(__CLASS__, 'searchform');
            return;
        }

        $searchTerms = $queryInfo['search'];

        $page = 1;
        if (isset($queryInfo['page']) === TRUE) {
            $page = $queryInfo['page'];
        }

        $model = self::getModel(__CLASS__);
        if ($model === FALSE) {
            trigger_error('Unable to get search model', E_USER_ERROR);
            exit;
        }

        /**
         * If the model can't do a search, it will throw an exception.
         */
        try {
            $results = $model->search($searchTerms, $page);
        } catch (Exception $e) {
            trigger_error('Unable to search for '.$searchTerms.' and page '.$page.':'.$e->getMessage(), E_USER_ERROR);
            exit;
        }
    }
}

/* vim: set expandtab ts=4 sw=4: */

