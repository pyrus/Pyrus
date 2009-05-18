<?php
/**
 * Simulate the internet
 */
class Internet extends PEAR2_HTTP_Request
{
    public static $requestMap;

    /**
     * sets up the adapter
     *
     * @param  string $class adapter to use
     */
    public function __construct($url = null) 
    {
        $this->adapter = new Internet_Adapter($this);
        if ($url) {
            $this->url = $url;
        }
    }

    function addDirectory($dir, $urlbase)
    {
        self::$requestMap[$urlbase] = $dir;
    }
}

class Internet_Exception extends Exception {}

class Internet_Adapter extends PEAR2_HTTP_Request_Adapter
{
    public $internet;
    function __construct($internet)
    {
        $this->internet = $internet;
    }

    /**
     * Send the request
     *
     * This function sends the actual request to the
     * remote/local webserver using pecl http
     *
     * @link http://us2.php.net/manual/en/http.request.options.php
     * @todo catch exceptions from HttpRequest and rethrow
     * @todo handle Puts
     */
    public function sendRequest() 
    {
        $uri = $this->uri->url;
        $actualfile = false;
        foreach (Internet::$requestMap as $urlbase => $dir) {
            if (strpos($urlbase, str_replace('\\', '/', $uri)) === 0) {
                $actualfile = $dir . DIRECTORY_SEPARATOR . substr($urlbase, strlen($uri)+1);
                break;
            }
        }
        if (!$actualfile) {
            throw new Internet_Exception('URL ' . $uri . ' is not in the request map, setup is needed');
        }
        if (!file_exists($actualfile)) {
            $details['code'] = '404';
            $body = '';
        } else {
            $body = file_get_contents($actualfile);
            $details['code'] = '200';
        }
        // $this->verb is GET/POST/etc.
        // $this->uri is PEAR2_HTTP_Request_Uri
        // $this->headers is array of headers
        // $this->body is request body

        $details = $this->uri->toArray();
        $details['httpVersion'] = 'HTTP/1.1';
        $cookies = array();

        return new PEAR2_HTTP_Request_Response($details, $body, $headers, $cookies);
    }	   
}
