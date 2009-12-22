<?php
/**
 * \pear2\Pyrus\Channel
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
namespace pear2\Pyrus;
class Channel implements \pear2\Pyrus\ChannelInterface
{
    protected $internal;
    
    /**
     * Construct a \pear2\Pyrus\Channel object
     *
     */
    function __construct(\pear2\Pyrus\ChannelFileInterface $info)
    {
        $this->internal = $info;
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
}
