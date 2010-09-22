<?php
namespace PEAR2\HTTP\Request\Adapter;
use PEAR2\HTTP\Request;
class Http extends Request\Adapter
{
    protected $request;
    protected $sentFilesize = false;
    /**
     * Throws exception if allow_url_fopen is off
     */
    public function __construct()
    {
        if (!extension_loaded('http')) {
            throw new Request\Exception(
                'The http extension must be loaded in order to use the Peclhttp adapter'
            );
        }
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
        $options = array(
            'connecttimeout'    => $this->requestTimeout,
        );

        // if we have any listeners register an onprogress callback
        if (count($this->_listeners) > 0) {
            $options['onprogress'] = array($this,'_onprogress');
        }

        $tmp = 'HTTP_METH_'.strtoupper($this->verb);
        if (defined($tmp)) {
            $method = constant($tmp);
        }
        else {
            $method = HTTP_METH_GET;
        }

        $this->request = $request = new \HttpRequest($this->uri->url, $method, $options);
        $request->setHeaders($this->headers);
        if ($this->body) {
            $request->setRawPostData($this->body);
        }

        $request->send();
        $response = $request->getResponseMessage();
        $body = $response->getBody();

        $details = $this->uri->toArray();

        $details['code'] = $request->getResponseCode();
        $details['httpVersion'] = $response->getHttpVersion();

        $headers = new Request\Headers($response->getHeaders());
        $cookies = $request->getResponseCookies();


        return new Request\Response($details, $body, $headers, $cookies);
    }      

    /**
     * Progress handler maps callback progress to listeners
     * @todo implement progress callback
     * @todo this doesn't want to be part of the public api but has to be public to be called as a callback
     */
    public function _onprogress($status)
    {
        $dltotal = $status['dltotal'];
        $dlnow = $status['dlnow'];

        // pecl http kind of sucks, no way to get the http response code until after the
        // callback completes...
        $this->_notify('filesize', $dltotal);
        $this->_notify('downloadprogress', $dlnow);
    }
}
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
