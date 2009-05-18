<?php
/**
 * Simulate the internet
 */
class Internet extends PEAR2_HTTP_Request
{
    public $requestMap;

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

    function addRequest($verb, $url, $headers, $responseheaders, $body, $code = '200')
    {
        $isfile = file_exists($body);
        $this->requestMap[$verb][$url][serialize($headers)] = array(
            'headers' => $responseheaders,
            'body' => $body,
            'code' => $code,
            'isfile' => $isfile
        );
    }
}

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
        // $this->verb is GET/POST/etc.
        // $this->uri is PEAR2_HTTP_Request_Uri
        // $this->headers is array of headers
        // $this->body is request body
        if (isset($this->internet->requestMap[$this->verb][$this->uri->url][serialize($this->headers)])) {
            $info = $this->internet->requestMap[$this->verb][$this->uri->url][serialize($this->headers)];
            $body = $info['isfile'] ? file_get_contents($info['body']) : $info['body'];
            $headers = $info['headers'];
            $details['code'] = $info['code'];
        } else {
            $details['code'] = '404';
            $body = '';
        }

        $details = $this->uri->toArray();

        $details['httpVersion'] = 'HTTP/1.1';

        $cookies = array();


        return new PEAR2_HTTP_Request_Response($details, $body, $headers, $cookies);
    }	   
}
