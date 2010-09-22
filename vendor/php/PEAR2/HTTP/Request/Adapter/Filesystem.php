<?php
namespace PEAR2\HTTP\Request\Adapter;
use PEAR2\HTTP\Request;
class Filesystem extends Request\Adapter
{
    public static $requestMap;
    
    /**
     * Add a local filesystem directory, and map it to a url base.
     * 
     * <code>
     * PEAR2\HTTP\Request\Adapter\Filesystem::addDirectory(
     *                  '/var/www/html/pear2.php.net',
     *                  'http://pear2.php.net/');
     * </code>
     * 
     * @param string $dir     A local directory filename
     * @param string $urlbase The base url to map this directory to
     * 
     * @return void
     */
    static function addDirectory($dir, $urlbase)
    {
        static::$requestMap[$urlbase] = realpath($dir);
    }

    /**
     * Send the request
     *
     * This function retrieves a file from the local filesystem which matches
     * the requested URL. If the uri does not match a urlbase set using the 
     * addDirectory method, an exception is thrown.
     * 
     * @throws Exception
     */
    public function sendRequest() 
    {
        $uri = $this->uri->url;
        $actualfile = false;
        foreach (static::$requestMap as $urlbase => $dir) {
            if (strpos($uri, $urlbase) === 0) {
                $actualfile = $dir . DIRECTORY_SEPARATOR . substr($uri, strlen($urlbase));
                break;
            }
        }
        if (!$actualfile) {
            throw new Request\Exception('URL ' . $uri . ' is not in the request map, setup is needed');
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
        // $this->uri is PEAR2\HTTP\Request\Uri
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

        return new Request\Response($details, $body, $headers, $cookies);
    }
}