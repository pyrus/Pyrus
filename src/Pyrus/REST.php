<?php
/**
 * PEAR_REST
 *
 * PHP versions 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Intelligently retrieve data, following hyperlinks if necessary, and re-directing
 * as well
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace pear2\Pyrus;
class REST
{
    protected $config;
    protected $_options;

    function __construct()
    {
        $this->config = Config::current();
        $this->_options = Main::$options;
    }

    /**
     * Retrieve REST data, but always retrieve the local cache if it is available.
     *
     * This is useful for elements that should never change, such as information on a particular
     * release
     *
     * @param string full URL to this resource
     * @param array|false contents of the accept-encoding header
     * @param boolean     if true, xml will be returned as a string, otherwise, xml will be
     *                    parsed using pear2\Pyrus\XMLParser
     *
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
     *
     * @param string full URL to this resource
     * @param array|false contents of the accept-encoding header
     * @param boolean     if true, xml will be returned as a string, otherwise, xml will be
     *                    parsed using pear2\Pyrus\XMLParser
     *
     * @return string|array
     * 
     * @throws pear2\Pyrus\REST\Exception If the xml cannot be parsed
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
            } catch (\pear2\HTTP\Request\Exception $e) {
                $file = $trieddownload = false;
            }
        } else {
            $file = $trieddownload = false;
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
            $headers      = $file[2];
            $lastmodified = $file[1];
            $content      = $file[0];
        } else {
            $content      = $file;
            $lastmodified = false;
            $headers      = array();
        }

        if ($forcestring) {
            $this->saveCache($url, $content, $lastmodified, false, $cacheId);
            return $content;
        }

        // Default to XML if no content-type is provided
        $ct = isset($headers['content-type']) ? $headers['content-type'] : 'text/xml';
        switch ($ct) {
            case 'text/xml' :
            case 'application/xml' :
                $parser = new XMLParser;
                try {
                    $content = $parser->parseString($content);
                    $content = current($content);
                } catch (\Exception $e) {
                    throw new REST\Exception('Invalid xml downloaded from "' . $url . '"', $e);
                }
            case 'text/html' :
            default :
                // use it as a string
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

    /**
     * retrieve a url stored in the cache
     * 
     * @param string $url full URL to REST resource
     *
     * @return string contents of file
     * 
     * @throws pear2\Pyrus\REST\Exception if no cache exists
     */
    function getCache($url)
    {
        $cachefile = $this->config->cache_dir . DIRECTORY_SEPARATOR .
            md5($url) . 'rest.cachefile';
        if (file_exists($cachefile)) {
            return unserialize(implode('', file($cachefile)));
        }

        throw new REST\Exception('No cached content available for "' . $url . '"');
    }

    /**
     * @param string $url          full URL to REST resource
     * @param string $contents     original contents of the REST resource
     * @param array  $lastmodified HTTP Last-Modified and ETag headers
     * @param bool   $nochange     if true, then the cache id file should be
     *                             regenerated to trigger a new time-to-live value
     * @param string $cacheid      optional filename of the cache file
     *
     * @return bool  Returns true on success, false on error
     * 
     * @throws pear2\Pyrus\REST\Exception
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
                throw new REST\Exception('Cannot create REST cache directory ' . $cache_dir);
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
     * If an HTTP proxy has been configured (http_proxy pear2\Pyrus\Config
     * setting), the proxy will be used.
     *
     * @param string             $url          the URL to download
     * @param false|string|array $lastmodified header values to check against
     *                                         for caching use false to return
     *                                         the header values from this download
     * @param false|array        $accept       Accept headers to send
     *
     * @return string|array  Returns the contents of the downloaded file or a PEAR
     *                       error on failure.  If the error is caused by
     *                       socket-related errors, the error object will
     *                       have the fsockopen error code available through
     *                       getCode().  If caching is requested, then return the header
     *                       values.
     *
     * @throws pear2\Pyrus\REST\Exception if the url is invalid
     * @access public
     */
    function downloadHttp($url, $lastmodified = null, $accept = false)
    {
        $info = parse_url($url);
        if (!isset($info['scheme']) || !in_array($info['scheme'], array('http', 'https'))) {
            throw new REST\Exception('Cannot download non-http URL "' . $url . '"');
        }

        if (!isset($info['host'])) {
            throw new REST\Exception('Cannot download from non-URL "' . $url . '"');
        }

        $response = Main::download($url);
        if ($response->code == 304 && ($lastmodified || ($lastmodified === false))) {
            return false;
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
}