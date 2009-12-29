<?php
/**
 * \pear2\Pyrus\ChannelFile\v1
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
 * Base class for a PEAR channel.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus\ChannelFile;
class v1 extends \pear2\Pyrus\ChannelFile implements \pear2\Pyrus\ChannelFileInterface
{
    /**
     * Supported channel.xml versions, for parsing
     * @var array
     */
    protected $supportedVersions = array('1.0');

    /**
     * Parsed channel information
     * @var array
     */
    protected $channelInfo = array(
        'attribs' => array(
            'version' => '1.0',
            'xmlns' => 'http://pear.php.net/channel-1.0',
        ),
    );

    public $rootAttributes = array(
            'version' => '1.0',
            'xmlns' => 'http://pear.php.net/channel-1.0',
            'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:schemaLocation' => 'http://pear.php.net/channel-1.0
http://pear.php.net/dtd/channel-1.0.xsd'
        );

    private $_xml;

    /**
     * Mapping of __get variables to method handlers
     * @var array
     */
    protected $getMap = array(
        'ssl' => 'getSSL',
        'port' => 'getPort',
        'suggestedalias' => 'getSuggestedAlias',
        'alias' => 'getAlias',
        'name' => 'getName',
        'mirror' => 'getServers',
        'mirrors' => 'getServers',
        'protocols' => 'getProtocols'
    );

    protected $setMap = array(
        'name' => 'setName',
        'ssl' => 'setSSL',
        'summary' => 'setSummary',
        'alias' => 'setAlias',
        'localalias' => 'setLocalAlias',
        'port' => 'setPort',
        'rawrest' => 'setREST',
        'rawmirrors' => 'setMirrors',
    );

    function __construct(array $data = null)
    {
        if (null !== $data) {
            $this->fromArray($data);
        }
    }


    /**
     * Directly set the channel info.
     *
     * @param array $data The xml parsed data
     */
    function fromArray($data)
    {
        if (isset($data['channel'])) {
            $this->channelInfo = $data['channel'];
        } else {
            $this->channelInfo = $data;
        }
        // Reset root attributes.
        $this->channelInfo['attribs'] = $this->rootAttributes;
    }

    /**
     * Validate the xml against the channel schema.
     *
     */
    function validate()
    {
        if (!isset($this->_xml)) {
            $this->__toString();
        }
        $a = new \pear2\Pyrus\XMLParser;
        $schema = \pear2\Pyrus\Main::getDataPath() . '/channel-1.0.xsd';
        // for running out of svn
        if (!file_exists($schema)) {
            $schema = dirname(dirname(dirname(__DIR__))) . '/data/channel-1.0.xsd';
        }
        try {
            $a->parseString($this->_xml, $schema);
            return true;
        } catch (\Exception $e) {
            throw new \pear2\Pyrus\Channel\Exception('Invalid channel.xml', $e);
        }
    }

    /**
     * Returns the raw xml for the channel file.
     *
     * @return string
     */
    function __toString()
    {
        return $this->_xml = (string) new \pear2\Pyrus\XMLWriter(array('channel'=>$this->channelInfo));
    }

    /**
     * @return string|false
     */
    protected function getName()
    {
        if (isset($this->channelInfo['name'])) {
            return $this->channelInfo['name'];
        }

        return false;
    }

    /**
     * @return int|80 port number to connect to
     */
    protected function getPort()
    {
        if (isset($this->channelInfo['servers']['primary']['attribs']['port'])) {
            return (int)$this->channelInfo['servers']['primary']['attribs']['port'];
        }

        if ($this->getSSL()) {
            return 443;
        }

        return 80;
    }

    /**
     * @return bool Determines whether secure sockets layer (SSL) is used to connect to this channel
     */
    protected function getSSL()
    {
        if (isset($this->channelInfo['servers']['primary']['attribs']['ssl'])) {
            return true;
        }

        return false;
    }

    function __get($var)
    {
        if (isset($this->getMap[$var])) {
            return $this->{$this->getMap[$var]}($var);
        }
        if ($var == 'remotepackages') {
            return new \pear2\Pyrus\Channel\Remotepackages($this);
        } elseif ($var == 'remotepackage') {
            return new \pear2\Pyrus\Channel\Remotepackage($this, false);
        } elseif ($var == 'remotecategories') {
            return new \pear2\Pyrus\Channel\Remotecategories($this);
        }
        if (!isset($this->channelInfo[$var])) {
            return null;
        }
        return $this->channelInfo[$var];
    }

    /**
     * Returns the protocols supported by the primary server for this channel
     *
     * @return \pear2\Pyrus\ChannelFile\v1\Servers\Protocols
     */
    protected function getProtocols()
    {
        if (isset($this->channelInfo['name']) && $this->channelInfo['name'] == '__uri') {
            throw new \pear2\Pyrus\Channel\Exception('__uri pseudo-channel has no protocols');
        }
        if (!isset($this->channelInfo['servers']) || !isset($this->channelInfo['servers']['primary'])) {
            return new \pear2\Pyrus\ChannelFile\v1\Servers\Protocols(array(), $this);
        }
        return new \pear2\Pyrus\ChannelFile\v1\Servers\Protocols($this->channelInfo['servers']['primary'], $this);
    }

    protected function getServers()
    {
        if ($this->channelInfo['name'] == '__uri') {
            throw new \pear2\Pyrus\Channel\Exception('__uri pseudo-channel cannot have mirrors');
        }
        if (isset($this->channelInfo['servers'])) {
            $servers = $this->channelInfo['servers'];
        } else {
            $servers = array();
        }
        return new \pear2\Pyrus\ChannelFile\v1\Servers($servers, $this);
    }

    /**
     * Determines whether a channel supports Representational State Transfer (REST) protocols
     * for retrieving channel information
     *
     * @return bool
     */
    function supportsREST()
    {
        return isset($this->channelInfo['servers']['primary']['rest']);
    }

    function __set($var, $value)
    {
        if (isset($this->setMap[$var])) {
            return $this->{$this->setMap[$var]}($value);
        }
        if (method_exists($this, "set$var")) {
            $sv = "set$var";
            $this->$sv($value);
        }
    }

    /**
     * Empty all REST definitions
     */
    function resetREST()
    {
        if (isset($this->channelInfo['servers']['primary']['rest'])) {
            unset($this->channelInfo['servers']['primary']['rest']);
        }
    }

    /**
     * @param string
     * @return string|false
     * @error PEAR_CHANNELFILE_ERROR_NO_NAME
     * @error PEAR_CHANNELFILE_ERROR_INVALID_NAME
     */
    protected function setName($name)
    {
        if (empty($name)) {
            throw new \pear2\Pyrus\Channel\Exception('Primary server must be non-empty');
        }
        if (!$this->validChannelServer($name)) {
            throw new \pear2\Pyrus\Channel\Exception('Primary server "' . $name .
                '" is not a valid channel server');
        }
        $this->channelInfo['name'] = $name;
    }

    /**
     * Test whether a string contains a valid channel server.
     * @param string $ver the package version to test
     * @return bool
     */
    static function validChannelServer($server)
    {
        if ($server == '__uri') {
            return true;
        }

        $regex = '/^[a-z0-9\-]+(?:\.[a-z0-9\-]+)*(\/[a-z0-9\-]+)*\\z/i';
        return (bool) preg_match($regex, $server);
    }

    /**
     * Set the socket number (port) that is used to connect to this channel
     * @param integer
     */
    protected function setPort($port)
    {
        if (isset($this->channelInfo['servers']) &&
              isset($this->channelInfo['servers']['primary']) &&
              !isset($this->channelInfo['servers']['primary']['attribs'])) {
            $this->channelInfo['servers']['primary'] =
                array_merge(array('attribs' => array('port' => $port)),
                            $this->channelInfo['servers']['primary']);
        } else {
            $this->channelInfo['servers']['primary']['attribs']['port'] = $port;
        }
    }

    /**
     * Set the socket number (port) that is used to connect to this channel
     * @param bool Determines whether to turn on SSL support or turn it off
     */
    protected function setSSL($ssl = true)
    {
        if ($ssl) {
        if (isset($this->channelInfo['servers']) &&
              isset($this->channelInfo['servers']['primary']) &&
              !isset($this->channelInfo['servers']['primary']['attribs'])) {
                $this->channelInfo['servers']['primary'] =
                    array_merge(array('attribs' => array('ssl' => 'yes')),
                                $this->channelInfo['servers']['primary']);
            } else {
                $this->channelInfo['servers']['primary']['attribs']['ssl'] = 'yes';
            }
        } elseif (isset($this->channelInfo['servers']['primary']['attribs']['ssl'])) {
            unset($this->channelInfo['servers']['primary']['attribs']['ssl']);
        }
    }

    /**
     * @param string
     * @return boolean success
     * @error PEAR_CHANNELFILE_ERROR_NO_SUMMARY
     * @warning PEAR_CHANNELFILE_ERROR_MULTILINE_SUMMARY
     */
    protected function setSummary($summary)
    {
        if (empty($summary)) {
            throw new \pear2\Pyrus\Channel\Exception('Channel summary cannot be empty');
        } elseif (strpos(trim($summary), "\n") !== false) {
            throw new \pear2\Pyrus\Channel\Exception('Channel summary cannot be multi-line');
        }
        $this->channelInfo['summary'] = $summary;
        return true;
    }

    protected function setLocalAlias($alias)
    {
        return $this->setAlias($alias, true);
    }

    /**
     * @param string
     * @param boolean determines whether the alias is in channel.xml or local
     * @return boolean success
     */
    protected function setAlias($alias, $local = false)
    {
        if (!$this->validChannelServer($alias)) {
            throw new \pear2\Pyrus\Channel\Exception('Alias "' . $alias . '" is not a valid channel alias');
        }

        $a = $local ? 'localalias' : 'suggestedalias';
        $this->channelInfo[$a] = $alias;
        return true;
    }

    protected function getSuggestedAlias()
    {
        if (isset($this->channelInfo['suggestedalias'])) {
            return $this->channelInfo['suggestedalias'];
        }

        return '';
    }

    /**
     * @return string
     */
    protected function getAlias()
    {
        if (isset($this->channelInfo['localalias'])) {
            return $this->channelInfo['localalias'];
        }

        if (isset($this->channelInfo['suggestedalias'])) {
            return $this->channelInfo['suggestedalias'];
        }

        if (isset($this->channelInfo['name'])) {
            return $this->channelInfo['name'];
        }

        return '';
    }

    /**
     * Set the package validation object if it differs from PEAR's default
     * The class must be includeable via changing _ in the classname to path separator,
     * but no checking of this is made.
     * @param string|false pass in false to reset to the default packagename regex
     * @return boolean success
     */
    function setValidationPackage($validateclass, $version)
    {
        if (empty($validateclass)) {
            unset($this->channelInfo['validatepackage']);
        }
        $this->channelInfo['validatepackage'] = array('_content' => $validateclass);
        $this->channelInfo['validatepackage']['attribs'] = array('version' => $version);
    }

    protected function setREST($rest)
    {
        if ($rest === null) {
            $this->resetREST();
            return;
        }
        $this->channelInfo['servers']['primary']['rest'] = $rest;
    }

    protected function setMirrors($mirrors)
    {
        if ($mirrors === null) {
            if (isset($this->channelInfo['servers']['mirror'])) {
                unset($this->channelInfo['servers']['mirror']);
            }
            return;
        }
        $this->channelInfo['servers']['mirror'] = $mirrors;
    }

    /**
     * Retrieve the name of the validation package for this channel
     * @return string|false
     */
    function getValidationPackage()
    {
        if (!isset($this->channelInfo['validatepackage'])) {
            return array('attribs' => array('version' => 'default'),
                '_content' => 'PEAR_Validate');
        }

        $info = $this->channelInfo['validatepackage'];
        if (!is_array($info)) {
            $info = array('attribs' => array('version' => 'default'), '_content' => $info);
        }
        return $info;
    }

    function getArray()
    {
        return $this->channelInfo;
    }

    /**
     * Retrieve the object that can be used for custom validation
     * @param string|false the name of the package to validate.  If the package is
     *                     the channel validation package, \pear2\Pyrus\Validate
     *                     is returned
     * @return \pear2\Pyrus\Validate|false false is returned if the validation
     *         package cannot be located
     */
    function getValidationObject($package = false)
    {
        if (isset($this->channelInfo['validatepackage'])) {
            if ($package == $this->channelInfo['validatepackage']['_content']) {
                // channel validation packages are always validated by \pear2\Pyrus\Validate
                $val = new \pear2\Pyrus\Validate;
                return $val;
            }

            $vclass = str_replace(array('.', 'PEAR_', '_'),
                                  array('\\', 'pear2\Pyrus\\', '\\'),
                                  $this->channelInfo['validatepackage']['_content']);
            if (!class_exists($vclass, true)) {
                throw new \pear2\Pyrus\ChannelFile\Exception(
                    'Validation object ' . $this->channelInfo['validatepackage']['_content'] .
                    ' cannot be instantiated');
            }

            $val = new $vclass;
        } else {
            $val = new \pear2\Pyrus\Validate;
        }
        return $val;
    }

    /**
     * This function is used by the channel updater and retrieves a value set by
     * the registry, or the current time if it has not been set
     * @return string
     */
    function lastModified()
    {
        if (isset($this->channelInfo['_lastmodified'])) {
            return $this->channelInfo['_lastmodified'];
        }

        return time();
    }

    function toChannelFile()
    {
        return $this;
    }
}
