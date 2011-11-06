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
            template::setKeyword('search:search:term', 'Enter a search term');
            template::printTemplate(__CLASS__, 'search');
            return;
        }

        if (empty($queryInfo) === TRUE || isset($queryInfo['search']) === FALSE) {
            template::setKeyword('search:search:term', 'Enter a search term');
            template::printTemplate(__CLASS__, 'search');
            return;
        }

        $searchTerms = $queryInfo['search'];
        template::setKeyword('search:search:term', htmlspecialchars($searchTerms));

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
            $results = $model::search($searchTerms, $page);
        } catch (Exception $e) {
            trigger_error('Unable to search for '.$searchTerms.' and page '.$page.':'.$e->getMessage(), E_USER_ERROR);
            exit;
        }

        /**
         * Make sure we get an "OK" response.
         */
        if ($results['response'] !== 'ok') {
            trigger_error('Unable to search for '.$searchTerms.' and page '.$page.':'.$results['info']['message'].' (code '.$results['info']['code'].')', E_USER_ERROR);
            exit;
        }

        /**
         * No results? Nice and easy :)
         */
        if ($results['info']['total'] == 0) {
            template::printTemplate(__CLASS__, 'search_no_results');
            return;
        }

        if ($results['currentpage'] <= 1) {
            template::setKeyword('search:search:prevnumber', 1);
        } else {
            template::setKeyword('search:search:prevnumber', ($results['currentpage'] - 1));
        }

        if ($results['currentpage'] < $results['info']['pages']) {
            template::setKeyword('search:search:nextnumber', ($results['currentpage'] + 1));
        } else {
            template::setKeyword('search:search:nextnumber', $results['currentpage']);
        }

        $pagination = array();
        $numPagesToShow = 5;
        $startPage = 1;
        $endPage   = $numPagesToShow;
        $midPage   = floor($numPagesToShow / 2);
        if ($results['currentpage'] > $midPage) {
            $startPage = $results['currentpage'] - $midPage;
            $endPage   = $results['currentpage'] + $midPage;
        }
        for ($x = $startPage; $x <= $endPage; $x++) {
            $class = '';
            if ($x == $results['currentpage']) {
                $class = 'search_result_pagination_current';
            }
            $pagination[] = array(
                             'class'      => $class,
                             'pagenumber' => $x,
                            );
        }
        template::setKeyword('search:search:pagination', $pagination);
        template::setKeyword('search:search:paginationbottom', $pagination);
        template::setKeyword('search:search:currentpage', $results['currentpage']);
        template::setKeyword('search:search:totalpages', $results['info']['pages']);

       /**
         * Just in case there are weird characters or quotes etc we need to deal with..
         */
        foreach ($results['photos'] as $_idx => $photo) {
            $photo['title'] = htmlspecialchars($photo['title']);
        }

        template::setKeyword('search:search:total', $results['info']['total']);
        template::setKeyword('search:search:results', $results['photos']);
        template::printTemplate(__CLASS__, 'search_results');

    }
}

/* vim: set expandtab ts=4 sw=4: */

