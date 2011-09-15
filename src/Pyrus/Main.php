<?php
/**
 * PEAR2_Pyrus
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */

/**
 * Base class for Pyrus.
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus;
class Main
{
    const VERSION = '@PACKAGE_VERSION@';

    /**
     * Installer options.  Valid indices are:
     *
     * - upgrade (upgrade or install packages)
     * - optionaldeps (also automatically download/install optional deps)
     * - force
     * - packagingroot
     * - install-plugins
     * - nodeps
     * - downloadonly
     * @var array
     */
    static public $options = array();

    /**
     * For easy unit testing
     */
    static public $downloadClass = 'PEAR2\HTTP\Request';

    /**
     * For frontends to control
     */
    static public $downloadListener = 'Pyrus\DownloadProgressListener';

    static public $paranoid;

    static function getDataPath()
    {
        static $val = false;
        if ($val) {
            return $val;
        }

        $val = dirname(dirname(__DIR__)) . '/data/pyrus.net/Pyrus';
        if (!file_exists($val)) {
          // running out of git
          $val = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'data';
        }

        $val = realpath($val);
        return $val;
    }

    static function getSourcePath()
    {
        return dirname(__DIR__);
    }

    static function getSignature()
    {
        if (defined('PYRUS_SIG')) {
            // this is defined in the phar stub
            return array('hash' => PYRUS_SIG, 'hash_type' => PYRUS_SIGTYPE);
        }

        return false;
    }

    static function prepend($prepend, $path)
    {
        $path = $prepend . DIRECTORY_SEPARATOR . $path;
        $path = preg_replace('@/+|\\\\+@', DIRECTORY_SEPARATOR, $path);
        return $path;
    }

    static function downloadWithProgress($url)
    {
        return static::download($url, null, false, true);
    }

    /**
     * Efficiently download a file through HTTP.  Returns downloaded file as a string in-memory
     * This is best used for small files
     *
     * If an HTTP proxy has been configured (http_proxy PEAR_Config
     * setting), the proxy will be used.
     *
     * @param string  $url       the URL to download
     * @param false|string|array $lastmodified header values to check against for caching
     *                           use false to return the header values from this download
     * @param false|array $accept Accept headers to send
     *
     * @return PEAR2\HTTP\Request\Response Object representing the response
     *
     * @access public
     * @throws PEAR2\HTTPException on download error
     */
    static function download($url, $lastmodified = null, $accept = false, $doprogress = false)
    {
        $info = parse_url($url);
        $class = static::$downloadClass;
        $request = new $class($url);
        if ($doprogress) {
            $listenerclass = static::$downloadListener;
            $request->attach(new $listenerclass);
        }

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
        if (Config::current()->http_proxy) {
            $request->proxy = Config::current()->http_proxy;
            $proxy_user = isset($proxy['user']) ? urldecode($proxy['user']) : null;
            $proxy_pass = isset($proxy['pass']) ? urldecode($proxy['pass']) : null;
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

        $request->setHeader('User-Agent', 'Pyrus/@PACKAGE_VERSION@/PHP/' . PHP_VERSION);
        $username = Config::current()->username;
        $password = Config::current()->password;
        if ($username && $password) {
            $tmp = base64_encode("$username:$password");
            $request->setHeader('Authorization', 'Basic ' . $tmp);
        }

        $acceptHeader = $accept ? implode(', ', $accept) : '';
        $request->setHeader('Accept', $acceptHeader);

        $request->setHeader('Connection', 'close');
        $response = $request->sendRequest();
        if ($response->code >= 400) {
            if ($response->code == 404) {
                throw new HTTPException(
                    "Download of $url failed, file does not exist", $response->code);
            }

            throw new HTTPException(
                "File $url not valid (received: {$response->body})", $response->code);
        }

        if ($response->code === 0 && $response->body === false) {
            throw new HTTPException(
                "File $url not valid (received a invalid response)", 500);
        }

        return $response;
    }

    static function getParanoiaLevel(Config $config = null)
    {
        if (isset(self::$paranoid)) {
            return self::$paranoid;
        }

        if (null === $config) {
            $config = Config::current();
        }

        return $config->paranoia;
    }
}