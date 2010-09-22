<?php

/**
 * Simple class for parsing url/uris
 *
 * Basically compatiable with Net_URL2 propery names but not methods
 */
namespace PEAR2\HTTP\Request;
class Uri
{

    protected $pieces = array();
    protected $map = array(
            'url' => 'uri',
            'querystring' => 'query',
            'anchor' => 'fragment',
            'protocol' => 'scheme',
            );
    protected $schemes = array(
            'http' => 80,
            'https'=> 443,
        );

    public function __construct($uri)
    {
        $this->pieces = parse_url($uri);
        $this->pieces['uri'] = $uri;
        if (!isset($this->pieces['port'])) {
            if (isset($this->schemes[$this->pieces['scheme']])) {
                $this->pieces['port'] = $this->schemes[$this->pieces['scheme']];
            }
            else {
                $this->pieces['port'] = 80;
            }
        }
    }

    public function __get($key)
    {
        if (isset($this->map[$key])) {
            $key = $this->map[$key];
        }
        if (isset($this->pieces[$key])) {
            return $this->pieces[$key];
        }
        return null;
    }

    public function toArray()
    {
        return $this->pieces;
    }
}
