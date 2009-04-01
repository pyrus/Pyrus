<?php
/**
 * PEAR2_Pyrus_ChannelRegistry_Channel_Xml
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
 * A class that represents individual channels within an XML channel registry
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_ChannelRegistry_Channel_Xml extends PEAR2_Pyrus_Channel implements Countable
{
    private $_parent;
    function __construct(PEAR2_Pyrus_ChannelRegistry_Xml $parent, $data)
    {
        $this->_parent = $parent;
        parent::__construct($data);
    }

    public function toChannelObject()
    {
        $chan = new PEAR2_Pyrus_Channel(new PEAR2_Pyrus_ChannelFile((string) $this->parentChannel, true));
        return $chan;
    }

    function count()
    {
        $reg = new PEAR2_Pyrus_Registry($this->_parent->getPath());
        return count($reg->listPackages($this->_parent->name));
    }

    public function resetREST()
    {
        parent::resetREST();
        $this->_parent->update($this);
    }

    public function setName($name)
    {
        parent::setName($name);
        $this->_parent->update($this);
    }

    public function setPort($port)
    {
        parent::setPort($port);
        $this->_parent->update($this);
    }

    public function setSSL($ssl = true)
    {
        parent::setSSL($ssl);
        $this->_parent->update($this);
    }

    public function setPath($protocol, $path)
    {
        parent::setPath($protocol, $path);
        $this->_parent->update($this);
    }

    public function addFunction($type, $version, $name)
    {
        parent::addFunction($type, $version, $name);
        $this->_parent->update($this);
    }

    public function setBaseUrl($resourceType, $url)
    {
        parent::setBaseURL($resourceType, $url);
        $this->_parent->update($this);
    }

    public function setAlias($alias, $local = false)
    {
        @unlink($this->_parent->getAliasFile($this->getAlias()));
        parent::setAlias($alias, $local);
        file_put_contents($this->getAliasFile($alias), $this->getName());
    }
}
