<?php
/**
 * Simulate the internet
 */
class Internet extends \pear2\HTTP\Request
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

    static function addDirectory($dir, $urlbase)
    {
        self::$requestMap[$urlbase] = realpath($dir);
    }
}

class Internet_Exception extends Exception {}

class Internet_Adapter extends \pear2\HTTP\Request\Adapter
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
            if (strpos($uri, $urlbase) === 0) {
                $actualfile = $dir . DIRECTORY_SEPARATOR . substr($uri, strlen($urlbase));
                break;
            }
        }
        if (!$actualfile) {
            throw new Internet_Exception('URL ' . $uri . ' is not in the request map, setup is needed');
        }
        $details = $this->uri->toArray();
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

        $details['httpVersion'] = 'HTTP/1.1';
        $cookies = array();
        $headers = array();
        if ($details['code'] == '200') {
            $headers['content-disposition'] = 'filename="' . basename($actualfile) . '"';
            $headers['content-length'] = strlen($body);
            $headers['etag'] = md5($body);
            $headers['last-modified'] = date('Y-m-d H:i', filemtime($actualfile));
            $info = pathinfo($actualfile);
            switch ($info['extension']) {
                case 'xml' :
                    $headers['content-type'] = 'text/xml';
                    break;
                case 'txt' :
                    $headers['content-type'] = 'text/plain';
                    break;
                default :
                    $headers['content-type'] = 'application/octet-stream';
            }
        }

        return new \pear2\HTTP\Request\Response($details, $body, $headers, $cookies);
    }	   
}
