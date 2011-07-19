<?php
/**
 * \Pyrus\Channel
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
class Channel implements \Pyrus\ChannelInterface
{
    protected $internal;

    /**
     * Construct a \Pyrus\Channel object
     *
     */
    function __construct(ChannelFileInterface $info)
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
