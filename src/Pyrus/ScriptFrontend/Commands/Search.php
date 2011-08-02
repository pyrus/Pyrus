<?php
namespace Pyrus\ScriptFrontend\Commands;
class Search
{
    /**
     * URI to a pearhunt compatible search api
     *
     * @var string
     */
    protected static $search_uri = 'http://pyr.us/';

    function __construct()
    {
        
    }

    function query($query)
    {
        $uri = self::$search_uri . '?q=' . urlencode($query);
        if ($results = file_get_contents($uri)) {
            if ($results = json_decode($results, true)) {
                return $results;
            }
        }
        return false;
    }
}