<?php
/**
 * \PEAR2\Pyrus\ChannelRegistry\Mirror\Xml
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * A class for handling mirrors within an xml based channel registry.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus\ChannelRegistry\Mirror;
class Xml extends \PEAR2\Pyrus\ChannelFile\v1\Mirror
{
    private $_parent;
    function __construct(&$mirrorarray, \PEAR2\Pyrus\ChannelInterface $parent,
                         \PEAR2\Pyrus\ChannelRegistry $reg)
    {
        parent::__construct($mirrorarray, $parent);
        $this->_parent = $reg;
    }

    public function toChannelObject()
    {
        $chan = new \PEAR2\Pyrus\Channel(new \PEAR2\Pyrus\ChannelFile((string) $this->parentChannel, true));
        return $chan;
    }

    public function resetREST()
    {
        parent::resetREST();
        $this->_parent->update($this->parentChannel);
    }

    public function setName($name)
    {
        parent::setName($name);
        $this->_parent->update($this->parentChannel);
    }

    public function setPort($port)
    {
        parent::setPort($port);
        $this->_parent->update($this->parentChannel);
    }

    public function setSSL($ssl = true)
    {
        parent::setSSL($ssl);
        $this->_parent->update($this->parentChannel);
    }

    public function setPath($protocol, $path)
    {
        parent::setPath($protocol, $path);
        $this->_parent->update($this->parentChannel);
    }

    public function addFunction($type, $version, $name)
    {
        parent::addFunction($type, $version, $name);
        $this->_parent->update($this->parentChannel);
    }

    public function setBaseUrl($resourceType, $url)
    {
        parent::setBaseURL($resourceType, $url);
        $this->_parent->update($this->parentChannel);
    }
}