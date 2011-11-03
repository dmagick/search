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
    private static $_config = array();

    /**
     * Api url. This should probably be in the config, but for now we'll set it here.
     */
    private static $_flickrApiUrl = 'http://api.flickr.com/services/rest/';

    private function __construct()
    {
    }

    public static function getInstance($config=array())
    {
        if (self::$_instance === NULL) {
            $class              = __CLASS__;
            $instance           = new $class;
            $instance::$_config = $config;
            self::$_instance    = $instance;
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
    public static function search($searchTerms='', $page=-1)
    {

        if (empty($searchTerms) === TRUE) {
            return FALSE;
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

        $instance = self::$_instance;
        $config   = $instance::$_config;

        $apiRequest  = self::$_flickrApiUrl.'?method=flickr.photos.search&';
        $apiRequest .= 'api_key='.$config['flickrApiKey'].'&';
        $apiRequest .= 'per_page='.$config['perpage'].'&';
        $apiRequest .= 'page='.$pageNum.'&';
        $apiRequest .= 'text='.str_replace(' ', '+', $searchTerms);

        $errorNum = -1;
        $errorStr = NULL;
        // Curl gives better errors, so we'll try to use that.
        if (function_exists('curl_init') === TRUE) {
            $curl = curl_init($apiRequest);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($curl);
            $info     = curl_getinfo($curl);
            $errorNum = curl_errno($curl);
            $errorStr = curl_error($curl);
            curl_close($curl);
        } else {
            $response = file_get_contents($apiRequest);
        }

        if ($response === FALSE) {
            throw new Exception("Unable to get url: ".$apiRequest." . Error: ".$errorStr." (".$errorNum.")");
        }

        $responseXml = simplexml_load_string($response);

        // See http://www.flickr.com/services/api/flickr.photos.search.html

        $responseAttributes = $responseXml->attributes();
        $responseType       = $responseAttributes['stat'];

        $searchResults = array();
        $searchResults['response'] = (string)$responseType;

        if ($responseType === 'fail') {
            foreach ($responseXml->children() as $error) {
                $errorInfo = $error->attributes();
                $errorCode = $errorInfo['code'];
                $errorMsg  = $errorInfo['msg'];
            }
            $searchResults['info'] = array(
                                      'code'    => $errorCode,
                                      'message' => $errorMsg,
                                     );
            return $searchResults;
        }

        foreach ($responseXml->children() as $photoInfo) {
            $attributes = $photoInfo->attributes();
            $searchResults['info'] = array(
                                      'pages' => (int)$attributes['pages'],
                                      'total' => (int)$attributes['total'],
                                     );

            $searchResults['photos'] = array();
            foreach ($photoInfo->children() as $photo) {
                $attributes = $photo->attributes();

                $title = (string)$attributes['title'];

                // See http://www.flickr.com/services/api/misc.urls.html
                $baseUrl  = 'http://farm'.$attributes['farm'].'.static.flickr.com/';
                $baseUrl .= $attributes['server'].'/';
                $baseUrl .= $attributes['id'].'_';
                $thumbUrl = $baseUrl.$attributes['secret'].'_m.jpg';
                $fullUrl  = $baseUrl.$attributes['secret'].'_b.jpg';
                $searchResults['photos'][] = array(
                                              'thumbnail' => $thumbUrl,
                                              'fullurl'   => $fullUrl,
                                              'title'     => $title,
                                             );
            }
        }
        return $searchResults;
    }
}

/* vim: set expandtab ts=4 sw=4: */

