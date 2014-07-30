<?php
/**
 * driver that uses php http:// stream to do requests
 *
 * Loosely Based on PEAR HTTP_Response
 *
 * @version $Revision: 1.52 $
 */
namespace PEAR2\HTTP\Request\Adapter;
use PEAR2\HTTP\Request;
class PhpStream extends Request\Adapter
{
    private $_phpErrorStr;

    /**
     * Throws exception if allow_url_fopen is off
     */
    public function __construct()
    {
        if (ini_get('allow_url_fopen') == false) {
            throw new Request\Exception(
                'allow_url_fopen is off, the http:// stream wrapper will not function'
            );
        }
    }


    /**
     * Send the request
     *
     * This function sends the actual request to the
     * remote/local webserver using php streams.
     */
    public function sendRequest()
    {

        $proxyurl        = '';
        $request_fulluri = false;

        if (!is_null($this->proxy)) {
            $proxyurl        = $this->proxy->url;
            $request_fulluri = true;
        }
        $info = array(
                $this->uri->protocol => array(
                    'method' => $this->verb,
                    'content' => $this->body,
                    'header' => $this->buildHeaderString(),
                    'proxy'  => $proxyurl,
                    'ignore_errors' => true,
                    'max_redirects' => 3,
                    'request_fulluri' => $request_fulluri,
                )
            );
        // create context with proper junk

        $ctx = stream_context_create(
            $info
        );
        if (count($this->_listeners)) {
            stream_context_set_params($ctx, array('notification' => array($this, 'streamNotifyCallback')));
        }

        $fp = fopen($this->uri->url, 'rb', false, $ctx);
        if (!is_resource($fp)) {
            throw new Request\Exception('Url ' . $this->uri->url .
                            ' could not be opened (PhpStream Adapter)');
        } else {
            restore_error_handler();
        }

        stream_set_timeout($fp, $this->requestTimeout);
        $body = stream_get_contents($fp);

        if ($body === false) {
            throw new Request\Exception(
                'Url ' . $this->uri->url . ' did not return a response'
            );
        }

        $meta = stream_get_meta_data($fp);
        fclose($fp);

        $headers = $meta['wrapper_data'];

        $details = $this->uri->toArray();

        $tmp = $this->parseResponseCode($headers[0]);
        $details['code'] = $tmp['code'];
        $details['httpVersion'] = $tmp['httpVersion'];

        $cookies = array();
        $this->headers = $this->cookies = array();

        foreach($headers as $line) {
            $this->processHeader($line);
        }
        return new Request\Response(
            $details,$body,new Request\Headers($this->headers),$this->cookies);
    }

    /**
     * Build header String
     *
     * This method builds the header string
     * to be passed to the request.
     *
     * @return string $out  The headers
     */
    private function buildHeaderString()
    {
        $out = '';
        foreach($this->headers as $header => $value) {
            $out .= "$header: $value\r\n";
        }
        return $out;
    }

    /**
     * This has to be public to be used as a callback but its actually private
     */
    public function _errorHandler($errno,$errstr) {
        $this->_phpErrorStr = $errstr;
    }

    public function streamNotifyCallback($notification_code, $severity, $message, $message_code,
                                         $bytes_transferred, $bytes_max)
    {
        switch($notification_code) {
            case STREAM_NOTIFY_RESOLVE:
            case STREAM_NOTIFY_AUTH_REQUIRED:
            case STREAM_NOTIFY_FAILURE:
            case STREAM_NOTIFY_AUTH_RESULT:
                /* Ignore */
                break;
    
            case STREAM_NOTIFY_COMPLETED:
                $this->_notify('disconnect');
                break;

            case STREAM_NOTIFY_REDIRECTED:
                $this->_notify('redirect', $message);
                break;
    
            case STREAM_NOTIFY_CONNECT:
                $this->_notify('connect');
                break;
    
            case STREAM_NOTIFY_FILE_SIZE_IS:
                $this->_notify('filesize', $bytes_max);
                break;
    
            case STREAM_NOTIFY_MIME_TYPE_IS:
                $this->_notify('mime-type', $message);
                break;
    
            case STREAM_NOTIFY_PROGRESS:
                $this->_notify('downloadprogress', $bytes_transferred);
                break;
            }
    }
}
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
?>
