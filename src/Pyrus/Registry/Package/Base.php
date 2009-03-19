<?php
/**
 * PEAR2_Pyrus_Registry_Package_Base
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
 * Registry package class base
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
abstract class PEAR2_Pyrus_Registry_Package_Base extends PEAR2_Pyrus_PackageFile_v2 implements ArrayAccess, PEAR2_Pyrus_IPackageFile
{

    protected $packagename;
    protected $package;
    protected $channel;
    protected $reg;

    function __construct(PEAR2_Pyrus_Registry_Base $cloner)
    {
        $this->reg = $cloner;
    }

    function offsetExists($offset)
    {
        $info = PEAR2_Pyrus_Config::current()->channelregistry->parseName($offset);
        return $this->reg->exists($info['package'], $info['channel']);
    }

    function offsetGet($offset)
    {
        $this->packagename = $offset;
        $info = PEAR2_Pyrus_Config::current()->channelregistry->parseName($this->packagename);
        $this->package = $info['package'];
        $this->channel = $info['channel'];
        $intermediate = $this->reg->toPackageFile($info['package'], $info['channel']);
        parent::fromv2($intermediate);
        $ret = clone $this;
        unset($this->packagename);
        unset($this->package);
        unset($this->channel);
        return $ret;
    }

    function offsetSet($offset, $value)
    {
        $this->reg->install($value);
    }

    function offsetUnset($offset)
    {
        $info = PEAR2_Pyrus_Config::current()->channelregistry->parseName($offset);
        $this->reg->uninstall($info['package'], $info['channel']);
    }

    function __get($var)
    {
        if (!isset($this->packagename)) {
            throw new PEAR2_Pyrus_Registry_Exception('Attempt to retrieve ' . $var .
                ' from unknown package');
        }
        return parent::__get($var);
    }

    function __set($var, $value)
    {
        if (!isset($this->packagename)) {
            throw new PEAR2_Pyrus_Registry_Exception('Attempt to retrieve ' . $var .
                ' from unknown package');
        }
        parent::__set($var, $value);
        $this->reg->replace($this);
    }

    function getSchemaOK()
    {
        return true;
    }
}