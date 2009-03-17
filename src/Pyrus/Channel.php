<?php
/**
 * PEAR2_Pyrus_Channel
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */

/**
 * Base class for Pyrus.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Channel implements PEAR2_Pyrus_IChannel
{
    
    protected $internal;

    protected $channeldescription;
    
    /**
     * Construct a PEAR2_Pyrus_Channel object
     *
     * @param string $data Raw channel xml
     */
    function __construct($channeldescription, $forceremote = false)
    {
        $this->channeldescription = $channeldescription;
        $class = $this->_parseChannelDescription($this->channeldescription);
        $this->internal = new $class($this->channeldescription, $this);
    }
    
    function __get($var)
    {
        return $this->internal->$var;
    }
    
    function __set($var, $value)
    {
        $this->internal->$var = $value;
    }
    
    function __toString()
    {
        return $this->internal->__toString();
    }
    
    function __call($func, $args)
    {
        // delegate to the internal object
        return call_user_func_array(array($this->internal, $func), $args);
    }
    
    public function getValidationObject($package = false)
    {
        return $this->internal->getValidationObject($package);
    }
    
    public function getValidationPackage()
    {
        return $this->internal->getValidationPackage();
    }

    function _parseChannelDescription($channel)
    {
        if (is_array($channel)) {
            return 'PEAR2_Pyrus_ChannelFile_v1';
        }
        if (strpos($channel, 'http://') === 0) {
            return 'PEAR2_Pyrus_Channel_Remote';
        }
        if (substr($channel, 0, 5) == '<?xml') {
            return 'PEAR2_Pyrus_ChannelFile_v1';
        }
        try {
            if (@file_exists($channel) && @is_file($channel)) {
                $info = pathinfo($channel);
                if (!isset($info['extension']) || !strlen($info['extension'])) {
                    // guess based on first 4 characters
                    $f = @fopen($channel, 'r');
                    if ($f) {
                        $first4 = fread($f, 4);
                        fclose($f);
                        if ($first4 == '<?xml') {
                            return 'PEAR2_Pyrus_ChannelFile';
                        }
                    }
                } else {
                    switch (strtolower($info['extension'])) {
                        case 'xml' :
                            return 'PEAR2_Pyrus_ChannelFile';
                    }
                }
            }
            $xml_url = 'http://' . $channel . '/channel.xml';
            $http = new PEAR2_HTTP_Request($xml_url);
            try {
                $response = $http->sendRequest();
                $this->channeldescription = $response->body;
            } catch (Exception $e) {
                // try secure
                try {
                    $xml_url = 'https://' . $channel . '/channel.xml';
                    $http = new PEAR2_HTTP_Request($xml_url);
                    $response = $http->sendRequest();
                    $this->channeldescription = $response->body;
                } catch (Exception $u) {
                    // failed, re-throw original error
                    throw $e;
                }
            }
            return 'PEAR2_Pyrus_ChannelFile_v1';
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Channel_Exception('channel "' . $channel . '" is unknown', $e);
        }
    }
}
