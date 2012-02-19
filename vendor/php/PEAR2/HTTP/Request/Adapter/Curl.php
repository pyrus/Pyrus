<?php
namespace PEAR2\HTTP\Request\Adapter;
use PEAR2\HTTP\Request;
class Curl extends Request\Adapter
{
    static public $whichOne;
    protected $sentFilesize = false;
    protected $sentConnect = false;
    protected $sentContentType = false;

    protected $curl = false;
    protected $fp = false;

    public function sendRequest()
    {
        $this->_setupRequest();

        return $this->_sendRequest();
    }

    public function requestToFile($file)
    {
        $this->_setupRequest();

        $this->fp = fopen($file,'w');
        curl_setopt($this->curl,CURLOPT_FILE,$this->fp);

        return $this->_sendRequest();
    }

    /**
     * @todo error checking
     * @implement put
     */
    protected function _setupRequest()
    {
        $this->curl = curl_init($this->uri->url);
        // check error here

        // request timeout
        //curl_setopt($this->curl,CURLOPT_CONNECTTIMEOUT,$this->requestTimeout);
        curl_setopt($this->curl,CURLOPT_TIMEOUT,$this->requestTimeout);

        // progress callback
        if (count($this->_listeners) > 0) {
            curl_setopt($this->curl, CURLOPT_PROGRESSFUNCTION, array($this, 'progressCallback'));
            curl_setopt($this->curl, CURLOPT_NOPROGRESS, false);
        }

        // follow redirects ???
        // curl_setopt($this->curl,CURLOPT_FOLLOWLOCATION,???);

        // set http version (currently we are only letting you force 1.0 otherwise we let curl auto determine
        switch(strtolower($this->httpVersion))
        {
            case 'http/1.0':
                curl_setopt($this->curl,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0);
                break;
            case 'http/1.1':
            default:
                curl_setopt($this->curl,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_NONE);
                break;
        }

        // http verb
        if (strtoupper($this->verb) == 'PUT') {
            throw new Exception("HTTP put not implmented for Curl yet");
        }
        curl_setopt($this->curl,CURLOPT_CUSTOMREQUEST,$this->verb);

        // headers
        $headers = array();
        foreach ($this->headers as $field => $value) {
            $headers[] = $field . ': ' . $value;
        }
        curl_setopt($this->curl,CURLOPT_HTTPHEADER,$headers);

        // general stuff
        curl_setopt($this->curl,CURLOPT_BINARYTRANSFER,true);
        curl_setopt($this->curl,CURLOPT_RETURNTRANSFER,true);

        if (!is_null($this->proxy)) {
            curl_setopt($this->curl, CURLOPT_PROXY, $this->proxy->url);
        }

        // setup a callback to handle header info
        curl_setopt($this->curl,CURLOPT_HEADERFUNCTION,array($this,'_headerCallback'));

        // post data
        if (!empty($this->body)) {
            curl_setopt($this->curl,CURLOPT_POSTFIELDS,$this->body);
        }
    }

    protected function _sendRequest()
    {
        $body = curl_exec($this->curl);
        $this->_notify('disconnect');

        if (false === $body) {
            throw new Request\Exception(
                'Curl ' . curl_error($this->curl) . ' (' . curl_errno($this->curl) . ')'
            );
        }

        $this->sentFilesize = false;

        if ($this->fp !== false) {
            fclose($this->fp);
        }

        $details = $this->uri->toArray();


        $details['code'] = curl_getinfo($this->curl,CURLINFO_HTTP_CODE);
        //$details['httpVersion'] = $response->getHttpVersion();

        $headers = new Request\Headers($this->headers);
        $cookies = array();

        return new Request\Response($details, $body, $headers, $cookies);
    }

    protected function _headerCallback($curl,$data)
    {
        $this->processHeader(trim($data));
        return strlen($data);
    }

    function progressCallback($dltotal, $dlnow, $ultotal, $ulnow)
    {
        $code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        if ($code > 200) {
            return;
        }
        if (!$this->sentConnect) {
            $this->sentConnect = true;
            $this->_notify('connect');
        }
        if (!$this->sentContentType) {
            $content_type = curl_getinfo($this->curl, CURLINFO_CONTENT_TYPE);
            if ($content_type) {
                $this->sentContentType = true;
                $this->_notify('mime-type', $content_type);
            }
        }
        if (!$this->sentFilesize) {
            $filesize = curl_getinfo($this->curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
            // $filesize will be -1 until the "Content-Length" header
            // has been processed, if there is one in the response.
            // After that, $filesize == $dltotal.
            if ($filesize != -1) {
                $this->sentFilesize = true;
                $this->_notify('filesize', $filesize);
            }
        }
        $this->_notify('downloadprogress', $dlnow);
    }
}
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
