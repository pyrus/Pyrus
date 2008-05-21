<?php
/**
 * PEAR_REST
 *
 * PHP versions 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */

/**
 * Intelligently retrieve data, following hyperlinks if necessary, and re-directing
 * as well
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_REST
{
    protected $config;
    protected $_options;
    function __construct($options = array())
    {
        $this->config = PEAR2_Pyrus_Config::current();
        $this->_options = $options;
    }

    /**
     * Retrieve REST data, but always retrieve the local cache if it is available.
     *
     * This is useful for elements that should never change, such as information on a particular
     * release
     * @param string full URL to this resource
     * @param array|false contents of the accept-encoding header
     * @param boolean     if true, xml will be returned as a string, otherwise, xml will be
     *                    parsed using PEAR_XMLParser
     * @return string|array
     */
    function retrieveCacheFirst($url, $accept = false, $forcestring = false)
    {
        $cachefile = $this->config->cache_dir . DIRECTORY_SEPARATOR .
            md5($url) . 'rest.cachefile';
        if (file_exists($cachefile)) {
            return unserialize(implode('', file($cachefile)));
        }
        return $this->retrieveData($url, $accept, $forcestring);
    }

    /**
     * Retrieve a remote REST resource
     * @param string full URL to this resource
     * @param array|false contents of the accept-encoding header
     * @param boolean     if true, xml will be returned as a string, otherwise, xml will be
     *                    parsed using PEAR_XMLParser
     * @return string|array
     */
    function retrieveData($url, $accept = false, $forcestring = false)
    {
        $cacheId = $this->getCacheId($url);
        if ($ret = $this->useLocalCache($url, $cacheId)) {
            return $ret;
        }

        if (!isset($this->_options['offline'])) {
            $trieddownload = true;
            try {
                $file = $this->downloadHttp($url, $cacheId ? $cacheId['lastChange'] : false, $accept);
            } catch (PEAR2_HTTP_Request_Exception $e) {
                $trieddownload = false;
                $file = false;
            }
        } else {
            $trieddownload = false;
            $file = false;
        }
        if (!$file) {
            $ret = $this->getCache($url);
            if ($trieddownload) {
                // reset the age of the cache if the server says it was unmodified
                $this->saveCache($url, $ret, null, true, $cacheId);
            }
            return $ret;
        }
        if (is_array($file)) {
            $headers = $file[2];
            $lastmodified = $file[1];
            $content = $file[0];
        } else {
            $content = $file;
            $lastmodified = false;
            $headers = array();
        }
        if ($forcestring) {
            $this->saveCache($url, $content, $lastmodified, false, $cacheId);
            return $content;
        }
        if (isset($headers['content-type'])) {
            switch ($headers['content-type']) {
                case 'text/xml' :
                case 'application/xml' :
                    $parser = new PEAR2_Pyrus_XMLParser;
                    try {
                        $content = $parser->parseString($content);
                        $content = current($content);
                    } catch (Exception $e) {
                        throw new PEAR2_Pyrus_REST_Exception(
                            'Invalid xml downloaded from "' . $url . '"', $e);
                    }
                case 'text/html' :
                default :
                    // use it as a string
            }
        } else {
            // assume XML
            $parser = new PEAR2_Pyrus_XMLParser;
            try {
                $content = $parser->parseString($content);
                $content = current($content);
            } catch (Exception $e) {
                throw new PEAR2_Pyrus_REST_Exception(
                    'Invalid xml downloaded from "' . $url . '"', $e);
            }
        }
        $this->saveCache($url, $content, $lastmodified, false, $cacheId);
        return $content;
    }

    function useLocalCache($url, $cacheid = null)
    {
        if ($cacheid === null) {
            $cacheidfile = $this->config->cache_dir . DIRECTORY_SEPARATOR .
                md5($url) . 'rest.cacheid';
            if (!file_exists($cacheidfile)) {
                return false;
            }

            $cacheid = unserialize(implode('', file($cacheidfile)));
        }
        $cachettl = $this->config->cache_ttl;
        // If cache is newer than $cachettl seconds, we use the cache!
        if (time() - $cacheid['age'] < $cachettl) {
            return $this->getCache($url);
        }
        return false;
    }

    function getCacheId($url)
    {
        $cacheidfile = $this->config->cache_dir . DIRECTORY_SEPARATOR .
            md5($url) . 'rest.cacheid';
        if (file_exists($cacheidfile)) {
            $ret = unserialize(implode('', file($cacheidfile)));
            return $ret;
        }

        return false;
    }

    function getCache($url)
    {
        $cachefile = $this->config->cache_dir . DIRECTORY_SEPARATOR .
            md5($url) . 'rest.cachefile';
        if (file_exists($cachefile)) {
            return unserialize(implode('', file($cachefile)));
        }

        throw new PEAR2_Pyrus_REST_Exception(
                'No cached content available for "' . $url . '"');
    }

    /**
     * @param string full URL to REST resource
     * @param string original contents of the REST resource
     * @param array  HTTP Last-Modified and ETag headers
     * @param bool   if true, then the cache id file should be regenerated to
     *               trigger a new time-to-live value
     */
    function saveCache($url, $contents, $lastmodified, $nochange = false, $cacheid = null)
    {
        $cacheidfile = $this->config->cache_dir . DIRECTORY_SEPARATOR .
            md5($url) . 'rest.cacheid';
        $cachefile = $this->config->cache_dir . DIRECTORY_SEPARATOR .
            md5($url) . 'rest.cachefile';
        if ($cacheid === null && $nochange) {
            $cacheid = unserialize(implode('', file($cacheidfile)));
        }

        $fp = @fopen($cacheidfile, 'wb');
        if (!$fp) {
            $cache_dir = $this->config->cache_dir;
            if (is_dir($cache_dir)) {
                return false;
            }

            if (!@mkdir($cache_dir, 0755, true)) {
                throw new PEAR2_Pyrus_REST_Exception(
                    'Cannot create REST cache directory ' . $cache_dir);
            }

            $fp = @fopen($cacheidfile, 'wb');
            if (!$fp) {
                return false;
            }
        }

        if ($nochange) {
            fwrite($fp, serialize(array(
                'age'        => time(),
                'lastChange' => $cacheid['lastChange'],
                )));
            fclose($fp);
            return true;
        } else {
            fwrite($fp, serialize(array(
                'age'        => time(),
                'lastChange' => $lastmodified,
                )));
        }
        fclose($fp);

        $fp = @fopen($cachefile, 'wb');
        if (!$fp) {
            if (file_exists($cacheidfile)) {
                @unlink($cacheidfile);
            }
            return false;
        }
        fwrite($fp, serialize($contents));
        fclose($fp);
        return true;
    }

    /**
     * Efficiently Download a file through HTTP.  Returns downloaded file as a string in-memory
     * This is best used for small files
     *
     * If an HTTP proxy has been configured (http_proxy PEAR_Config
     * setting), the proxy will be used.
     *
     * @param string  $url       the URL to download
     * @param string  $save_dir  directory to save file in
     * @param false|string|array $lastmodified header values to check against for caching
     *                           use false to return the header values from this download
     * @param false|array $accept Accept headers to send
     * @return string|array  Returns the contents of the downloaded file or a PEAR
     *                       error on failure.  If the error is caused by
     *                       socket-related errors, the error object will
     *                       have the fsockopen error code available through
     *                       getCode().  If caching is requested, then return the header
     *                       values.
     *
     * @access public
     */
    function downloadHttp($url, $lastmodified = null, $accept = false)
    {
        $info = parse_url($url);
        if (!isset($info['scheme']) || !in_array($info['scheme'], array('http', 'https'))) {
            throw new PEAR2_Pyrus_REST_Exception('Cannot download non-http URL "' . $url . '"');
        }
        if (!isset($info['host'])) {
            throw new PEAR2_Pyrus_REST_Exception('Cannot download from non-URL "' . $url . '"');
        }
        $request = new PEAR2_HTTP_Request($url);
        $host = $info['host'];
        if (!array_key_exists('port', $info)) {
            $info['port'] = null;
        }
        if (!array_key_exists('path', $info)) {
            $info['path'] = null;
        }
        $port = $info['port'];
        $path = $info['path'];
        $proxy_host = $proxy_port = $proxy_user = $proxy_pass = '';
        if ($this->config->http_proxy &&
              $proxy = parse_url($this->config->http_proxy)) {
            $proxy_host = isset($proxy['host']) ? $proxy['host'] : null;
            if (isset($proxy['scheme']) && $proxy['scheme'] == 'https') {
                $proxy_host = 'ssl://' . $proxy_host;
            }
            $proxy_port = isset($proxy['port']) ? $proxy['port'] : 8080;
            $proxy_user = isset($proxy['user']) ? urldecode($proxy['user']) : null;
            $proxy_pass = isset($proxy['pass']) ? urldecode($proxy['pass']) : null;
            // TODO: implement this when HTTP_Request supports it
        }
        if (empty($port)) {
            if (isset($info['scheme']) && $info['scheme'] == 'https') {
                $port = 443;
            } else {
                $port = 80;
            }
        }

        if (is_array($lastmodified)) {
            if (isset($lastmodified['Last-Modified'])) {
                $request->setHeader('If-Modified-Since', $lastmodified['Last-Modified']);
            }
            if (isset($lastmodified['ETag'])) {
                $request->setHeader('If-None-Match', $lastmodified['ETag']);
            }
        } elseif ($lastmodified) {
            $request->setHeader('If-Modified-Since', $lastmodified);
        }
        $request->setHeader('User-Agent', 'PEAR2_Pyrus/@PACKAGE_VERSION@/PHP/' . PHP_VERSION);
        $username = $this->config->username;
        $password = $this->config->password;
        if ($username && $password) {
            $tmp = base64_encode("$username:$password");
            $request->setHeader('Authorization', 'Basic ' . $tmp);
        }
        if ($proxy_host != '' && $proxy_user != '') {
            $request->setHeader('Proxy-Authorization', 'Basic ' .
                base64_encode($proxy_user . ':' . $proxy_pass));
        }
        if ($accept) {
            $request->setHeader('Accept', implode(', ', $accept));
        } else {
            $request->setHeader('Accept', '');
        }
        $request->setHeader('Connection', 'close');
        $response = $request->sendRequest();
        if ($response->code == 304 && ($lastmodified || ($lastmodified === false))) {
            return false;
        }
        if ($response->code != 200) {
            throw new PEAR2_Pyrus_REST_HTTPException(
                "File http://$host:$port$path not valid (received: $line)", $response->code);
        }
        if (isset($response->headers['content-length'])) {
            $length = $response->headers['content-length'];
        } else {
            $length = -1;
        }
        $data = $response->body;
        if ($lastmodified === false || $lastmodified) {
            if (isset($response->headers['etag'])) {
                $lastmodified = array('ETag' => $response->headers['etag']);
            }
            if (isset($response->headers['last-modified'])) {
                if (is_array($lastmodified)) {
                    $lastmodified['Last-Modified'] = $response->headers['last-modified'];
                } else {
                    $lastmodified = $response->headers['last-modified'];
                }
            }
            return array($data, $lastmodified, $response->headers);
        }
        return $data;
    }
}s