<?php
class PEAR2_Pyrus_Channel implements PEAR2_Pyrus_IChannel
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

    /**
     * The serialized representation of this channel
     *
     * @var string
     */
    private $_xml;

    function validate()
    {
        if (!isset($this->_xml)) {
            $this->__toString();
        }
        $a = new PEAR2_Pyrus_XMLParser;
        try {
            $a->parseString($this->_xml, PEAR2_Pyrus_Config::current()->data_dir .
                '/pear.php.net/PEAR2_Pyrus/channel-1.0.xsd');
        } catch (Exception $e) {
            throw new PEAR2_Channel_Exception('Invalid channel.xml', $e);
        }
    }

    function __toString()
    {
        if (!isset($this->_xml)) {
            $this->_xml = (string) new PEAR2_Pyrus_XMLWriter($this->channelInfo);
        }
        return $this->_xml;
    }

    function toChannelObject()
    {
        return $this;
    }

    /**
     * @return string|false
     */
    function getName()
    {
        if (isset($this->_channelInfo['name'])) {
            return $this->_channelInfo['name'];
        } else {
            return false;
        }
    }

    /**
     * @return string|false
     */
    function getServer()
    {
        if (isset($this->_channelInfo['name'])) {
            return $this->_channelInfo['name'];
        } else {
            return false;
        }
    }

    /**
     * @return int|80 port number to connect to
     */
    function getPort($mirror = false)
    {
        if ($mirror) {
            if ($mir = $this->getMirror($mirror)) {
                if (isset($mir['attribs']['port'])) {
                    return $mir['attribs']['port'];
                } else {
                    if ($this->getSSL($mirror)) {
                        return 443;
                    }
                    return 80;
                }
            }
            return false;
        }
        if (isset($this->_channelInfo['servers']['primary']['attribs']['port'])) {
            return $this->_channelInfo['servers']['primary']['attribs']['port'];
        }
        if ($this->getSSL()) {
            return 443;
        }
        return 80;
    }

    /**
     * @return bool Determines whether secure sockets layer (SSL) is used to connect to this channel
     */
    function getSSL($mirror = false)
    {
        if ($mirror) {
            if ($mir = $this->getMirror($mirror)) {
                if (isset($mir['attribs']['ssl'])) {
                    return true;
                } else {
                    return false;
                }
            }
            return false;
        }
        if (isset($this->_channelInfo['servers']['primary']['attribs']['ssl'])) {
            return true;
        }
        return false;
    }

    /**
     * @return string|false
     */
    function getSummary()
    {
        if (isset($this->_channelInfo['summary'])) {
            return $this->_channelInfo['summary'];
        } else {
            return false;
        }
    }

    /**
     * @param string xmlrpc or soap
     * @param string|false mirror name or false for primary server
     */
    function getPath($protocol, $mirror = false)
    {   
        if (!in_array($protocol, array('xmlrpc', 'soap'))) {
            return false;
        }
        if ($mirror) {
            if (!($mir = $this->getMirror($mirror))) {
                return false;
            }
            if (isset($mir[$protocol]['attribs']['path'])) {
                return $mir[$protocol]['attribs']['path'];
            } else {
                return $protocol . '.php';
            }
        } elseif (isset($this->_channelInfo['servers']['primary'][$protocol]['attribs']['path'])) {
            return $this->_channelInfo['servers']['primary'][$protocol]['attribs']['path'];
        }
        return $protocol . '.php';
    }

    /**
     * @param string protocol type (xmlrpc, soap)
     * @param string Mirror name
     * @return array|false
     */
    function getFunctions($protocol, $mirror = false)
    {
        if ($this->getName() == '__uri') {
            return false;
        }
        if ($protocol == 'rest') {
            $function = 'baseurl';
        } else {
            $function = 'function';
        }
        if ($mirror) {
            if ($mir = $this->getMirror($mirror)) {
                if (isset($mir[$protocol][$function])) {
                    return $mir[$protocol][$function];
                }
            }
            return false;
        }
        if (isset($this->_channelInfo['servers']['primary'][$protocol][$function])) {
            return $this->_channelInfo['servers']['primary'][$protocol][$function];
        } else {
            return false;
        }
    }

    /**
     * @param string Protocol type
     * @param string Function name (null to return the
     *               first protocol of the type requested)
     * @param string Mirror name, if any
     * @return array
     */
     function getFunction($type, $name = null, $mirror = false)
     {
        $protocols = $this->getFunctions($type, $mirror);
        if (!$protocols) {
            return false;
        }
        foreach ($protocols as $protocol) {
            if ($name === null) {
                return $protocol;
            }
            if ($protocol['_content'] != $name) {
                continue;
            }
            return $protocol;
        }
        return false;
     }

    /**
     * @param string protocol type
     * @param string protocol name
     * @param string version
     * @param string mirror name
     * @return boolean
     */
    function supports($type, $name = null, $mirror = false, $version = '1.0')
    {
        $protocols = $this->getFunctions($type, $mirror);
        if (!$protocols) {
            return false;
        }
        foreach ($protocols as $protocol) {
            if ($protocol['attribs']['version'] != $version) {
                continue;
            }
            if ($name === null) {
                return true;
            }
            if ($protocol['_content'] != $name) {
                continue;
            }
            return true;
        }
        return false;
    }

    /**
     * Determines whether a channel supports Representational State Transfer (REST) protocols
     * for retrieving channel information
     * @param string
     * @return bool
     */
    function supportsREST($mirror = false)
    {
        if ($mirror == $this->_channelInfo['name']) {
            $mirror = false;
        }
        if ($mirror) {
            if ($mir = $this->getMirror($mirror)) {
                return isset($mir['rest']);
            }
            return false;
        }
        return isset($this->_channelInfo['servers']['primary']['rest']);
    }

    /**
     * Get the URL to access a base resource.
     *
     * Hyperlinks in the returned xml will be used to retrieve the proper information
     * needed.  This allows extreme extensibility and flexibility in implementation
     * @param string Resource Type to retrieve
     */
    function getBaseURL($resourceType, $mirror = false)
    {
        if ($mirror == $this->_channelInfo['name']) {
            $mirror = false;
        }
        if ($mirror) {
            if ($mir = $this->getMirror($mirror)) {
                $rest = $mir['rest'];
            } else {
                return false;
            }
        } else {
            $rest = $this->_channelInfo['servers']['primary']['rest'];
        }
        if (!isset($rest['baseurl'][0])) {
            $rest['baseurl'] = array($rest['baseurl']);
        }
        foreach ($rest['baseurl'] as $baseurl) {
            if (strtolower($baseurl['attribs']['type']) == strtolower($resourceType)) {
                return $baseurl['_content'];
            }
        }
        return false;
    }

    /**
     * Since REST does not implement RPC, provide this as a logical wrapper around
     * resetFunctions for REST
     * @param string|false mirror name, if any
     */
    function resetREST($mirror = false)
    {
        return $this->resetFunctions('rest', $mirror);
    }

    /**
     * Empty all protocol definitions
     * @param string protocol type (xmlrpc, soap)
     * @param string|false mirror name, if any
     */
    function resetFunctions($type, $mirror = false)
    {
        if ($mirror) {
            if (isset($this->_channelInfo['servers']['mirror'])) {
                $mirrors = $this->_channelInfo['servers']['mirror'];
                if (!isset($mirrors[0])) {
                    $mirrors = array($mirrors);
                }
                foreach ($mirrors as $i => $mir) {
                    if ($mir['attribs']['host'] == $mirror) {
                        if (isset($this->_channelInfo['servers']['mirror'][$i][$type])) {
                            unset($this->_channelInfo['servers']['mirror'][$i][$type]);
                        }
                        return true;
                    }
                }
                return false;
            } else {
                return false;
            }
        } else {
            if (isset($this->_channelInfo['servers']['primary'][$type])) {
                unset($this->_channelInfo['servers']['primary'][$type]);
            }
            return true;
        }
    }

    /**
     * Set a channel's protocols to the protocols supported by pearweb
     */
    function setDefaultPEARProtocols($version = '1.0', $mirror = false)
    {
        switch ($version) {
            case '1.0' :
                $this->resetFunctions('xmlrpc', $mirror);
                $this->resetFunctions('soap', $mirror);
                $this->resetREST($mirror);
                $this->addFunction('xmlrpc', '1.0', 'logintest', $mirror);
                $this->addFunction('xmlrpc', '1.0', 'package.listLatestReleases', $mirror);
                $this->addFunction('xmlrpc', '1.0', 'package.listAll', $mirror);
                $this->addFunction('xmlrpc', '1.0', 'package.info', $mirror);
                $this->addFunction('xmlrpc', '1.0', 'package.getDownloadURL', $mirror);
                $this->addFunction('xmlrpc', '1.1', 'package.getDownloadURL', $mirror);
                $this->addFunction('xmlrpc', '1.0', 'package.getDepDownloadURL', $mirror);
                $this->addFunction('xmlrpc', '1.1', 'package.getDepDownloadURL', $mirror);
                $this->addFunction('xmlrpc', '1.0', 'package.search', $mirror);
                $this->addFunction('xmlrpc', '1.0', 'channel.listAll', $mirror);
                return true;
            break;
            default :
                return false;
            break;
        }
    }
    
    /**
     * @return array
     */
    function getMirrors()
    {
        if (isset($this->_channelInfo['servers']['mirror'])) {
            $mirrors = $this->_channelInfo['servers']['mirror'];
            if (!isset($mirrors[0])) {
                $mirrors = array($mirrors);
            }
            return $mirrors;
        } else {
            return array();
        }
    }

    /**
     * Get the unserialized XML representing a mirror
     * @return array|false
     */
    function getMirror($server)
    {
        foreach ($this->getMirrors() as $mirror) {
            if ($mirror['attribs']['host'] == $server) {
                return $mirror;
            }
        }
        return false;
    }

    /**
     * @param string
     * @return string|false
     * @error PEAR_CHANNELFILE_ERROR_NO_NAME
     * @error PEAR_CHANNELFILE_ERROR_INVALID_NAME
     */
    function setName($name)
    {
        return $this->setServer($name);
    }

    /**
     * Set the socket number (port) that is used to connect to this channel
     * @param integer
     * @param string|false name of the mirror server, or false for the primary
     */
    function setPort($port, $mirror = false)
    {
        if ($mirror) {
            if (!isset($this->_channelInfo['servers']['mirror'])) {
                $this->_validateError(PEAR_CHANNELFILE_ERROR_MIRROR_NOT_FOUND,
                    array('mirror' => $mirror));
                return false;
            }
            if (isset($this->_channelInfo['servers']['mirror'][0])) {
                foreach ($this->_channelInfo['servers']['mirror'] as $i => $mir) {
                    if ($mirror == $mir['attribs']['host']) {
                        $this->_channelInfo['servers']['mirror'][$i]['attribs']['port'] = $port;
                        return true;
                    }
                }
                return false;
            } elseif ($this->_channelInfo['servers']['mirror']['attribs']['host'] == $mirror) {
                $this->_channelInfo['servers']['mirror']['attribs']['port'] = $port;
                $this->_xml = null;
                return true;
            }
        }
        $this->_channelInfo['servers']['primary']['attribs']['port'] = $port;
        $this->_xml = null;
        return true;
    }

    /**
     * Set the socket number (port) that is used to connect to this channel
     * @param bool Determines whether to turn on SSL support or turn it off
     * @param string|false name of the mirror server, or false for the primary
     */
    function setSSL($ssl = true, $mirror = false)
    {
        if ($mirror) {
            if (!isset($this->_channelInfo['servers']['mirror'])) {
                $this->_validateError(PEAR_CHANNELFILE_ERROR_MIRROR_NOT_FOUND,
                    array('mirror' => $mirror));
                return false;
            }
            if (isset($this->_channelInfo['servers']['mirror'][0])) {
                foreach ($this->_channelInfo['servers']['mirror'] as $i => $mir) {
                    if ($mirror == $mir['attribs']['host']) {
                        if (!$ssl) {
                            if (isset($this->_channelInfo['servers']['mirror'][$i]
                                  ['attribs']['ssl'])) {
                                unset($this->_channelInfo['servers']['mirror'][$i]['attribs']['ssl']);
                            }
                        } else {
                            $this->_channelInfo['servers']['mirror'][$i]['attribs']['ssl'] = 'yes';
                        }
                        return true;
                    }
                }
                return false;
            } elseif ($this->_channelInfo['servers']['mirror']['attribs']['host'] == $mirror) {
                if (!$ssl) {
                    if (isset($this->_channelInfo['servers']['mirror']['attribs']['ssl'])) {
                        unset($this->_channelInfo['servers']['mirror']['attribs']['ssl']);
                    }
                } else {
                    $this->_channelInfo['servers']['mirror']['attribs']['ssl'] = 'yes';
                }
                $this->_xml = null;
                return true;
            }
        }
        if ($ssl) {
            $this->_channelInfo['servers']['primary']['attribs']['ssl'] = 'yes';
        } else {
            if (isset($this->_channelInfo['servers']['primary']['attribs']['ssl'])) {
                unset($this->_channelInfo['servers']['primary']['attribs']['ssl']);
            }
        }
        $this->_xml = null;
        return true;
    }

    /**
     * Set the socket number (port) that is used to connect to this channel
     * @param integer
     * @param string|false name of the mirror server, or false for the primary
     */
    function setPath($protocol, $path, $mirror = false)
    {
        if (!in_array($protocol, array('xmlrpc', 'soap'))) {
            return false;
        }
        if ($mirror) {
            if (!isset($this->_channelInfo['servers']['mirror'])) {
                $this->_validateError(PEAR_CHANNELFILE_ERROR_MIRROR_NOT_FOUND,
                    array('mirror' => $mirror));
                return false;
            }
            if (isset($this->_channelInfo['servers']['mirror'][0])) {
                foreach ($this->_channelInfo['servers']['mirror'] as $i => $mir) {
                    if ($mirror == $mir['attribs']['host']) {
                        $this->_channelInfo['servers']['mirror'][$i][$protocol]['attribs']['path'] =
                            $path;
                        return true;
                    }
                }
                $this->_validateError(PEAR_CHANNELFILE_ERROR_MIRROR_NOT_FOUND,
                    array('mirror' => $mirror));
                return false;
            } elseif ($this->_channelInfo['servers']['mirror']['attribs']['host'] == $mirror) {
                $this->_channelInfo['servers']['mirror'][$protocol]['attribs']['path'] = $path;
                $this->_xml = null;
                return true;
            }
        }
        $this->_channelInfo['servers']['primary'][$protocol]['attribs']['path'] = $path;
        $this->_xml = null;
        return true;
    }

    /**
     * @param string
     * @return string|false
     * @error PEAR_CHANNELFILE_ERROR_NO_SERVER
     * @error PEAR_CHANNELFILE_ERROR_INVALID_SERVER
     */
    function setServer($server, $mirror = false)
    {
        if (empty($server)) {
            $this->_validateError(PEAR_CHANNELFILE_ERROR_NO_SERVER);
            return false;
        } elseif (!$this->validChannelServer($server)) {
            $this->_validateError(PEAR_CHANNELFILE_ERROR_INVALID_NAME,
                array('tag' => 'name', 'name' => $server));
            return false;
        }
        if ($mirror) {
            $found = false;
            foreach ($this->_channelInfo['servers']['mirror'] as $i => $mir) {
                if ($mirror == $mir['attribs']['host']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->_validateError(PEAR_CHANNELFILE_ERROR_MIRROR_NOT_FOUND,
                    array('mirror' => $mirror));
                return false;
            }
            $this->_channelInfo['mirror'][$i]['attribs']['host'] = $server;
            return true;
        }
        $this->_channelInfo['name'] = $server;
        return true;
    }

    /**
     * @param string
     * @return boolean success
     * @error PEAR_CHANNELFILE_ERROR_NO_SUMMARY
     * @warning PEAR_CHANNELFILE_ERROR_MULTILINE_SUMMARY
     */
    function setSummary($summary)
    {
        if (empty($summary)) {
            $this->_validateError(PEAR_CHANNELFILE_ERROR_NO_SUMMARY);
            return false;
        } elseif (strpos(trim($summary), "\n") !== false) {
            $this->_validateWarning(PEAR_CHANNELFILE_ERROR_MULTILINE_SUMMARY,
                array('summary' => $summary));
        }
        $this->_channelInfo['summary'] = $summary;
        return true;
    }

    /**
     * @param string
     * @param boolean determines whether the alias is in channel.xml or local
     * @return boolean success
     */
    function setAlias($alias, $local = false)
    {
        if (!$this->validChannelServer($alias)) {
            $this->_validateError(PEAR_CHANNELFILE_ERROR_INVALID_NAME,
                array('tag' => 'suggestedalias', 'name' => $alias));
            return false;
        }
        if ($local) {
            $this->_channelInfo['localalias'] = $alias;
        } else {
            $this->_channelInfo['suggestedalias'] = $alias;
        }
        return true;
    }

    /**
     * @return string
     */
    function getAlias()
    {
        if (isset($this->_channelInfo['localalias'])) {
            return $this->_channelInfo['localalias'];
        }
        if (isset($this->_channelInfo['suggestedalias'])) {
            return $this->_channelInfo['suggestedalias'];
        }
        if (isset($this->_channelInfo['name'])) {
            return $this->_channelInfo['name'];
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
            unset($this->_channelInfo['validatepackage']);
        }
        $this->_channelInfo['validatepackage'] = array('_content' => $validateclass);
        $this->_channelInfo['validatepackage']['attribs'] = array('version' => $version);
    }

    /**
     * Add a protocol to the provides section
     * @param string protocol type
     * @param string protocol version
     * @param string protocol name, if any
     * @param string mirror name, if this is a mirror's protocol
     * @return bool
     */
    function addFunction($type, $version, $name = '', $mirror = false)
    {
        if ($mirror) {
            return $this->addMirrorFunction($mirror, $type, $version, $name);
        }
        $set = array('attribs' => array('version' => $version), '_content' => $name);
        if (!isset($this->_channelInfo['servers']['primary'][$type]['function'])) {
            if (!isset($this->_channelInfo['servers'])) {
                $this->_channelInfo['servers'] = array('primary' =>
                    array($type => array()));
            } elseif (!isset($this->_channelInfo['servers']['primary'])) {
                $this->_channelInfo['servers']['primary'] = array($type => array());
            }
            $this->_channelInfo['servers']['primary'][$type]['function'] = $set;
            $this->_xml = null;
            return true;
        } elseif (!isset($this->_channelInfo['servers']['primary'][$type]['function'][0])) {
            $this->_channelInfo['servers']['primary'][$type]['function'] = array(
                $this->_channelInfo['servers']['primary'][$type]['function']);
        }
        $this->_channelInfo['servers']['primary'][$type]['function'][] = $set;
        return true;
    }
    /**
     * Add a protocol to a mirror's provides section
     * @param string mirror name (server)
     * @param string protocol type
     * @param string protocol version
     * @param string protocol name, if any
     */
    function addMirrorFunction($mirror, $type, $version, $name = '')
    {
        if (!isset($this->_channelInfo['servers']['mirror'])) {
            $this->_validateError(PEAR_CHANNELFILE_ERROR_MIRROR_NOT_FOUND,
                array('mirror' => $mirror));
            return false;
        }
        $setmirror = false;
        if (isset($this->_channelInfo['servers']['mirror'][0])) {
            foreach ($this->_channelInfo['servers']['mirror'] as $i => $mir) {
                if ($mirror == $mir['attribs']['host']) {
                    $setmirror = &$this->_channelInfo['servers']['mirror'][$i];
                    break;
                }
            }
        } else {
            if ($this->_channelInfo['servers']['mirror']['attribs']['host'] == $mirror) {
                $setmirror = &$this->_channelInfo['servers']['mirror'];
            }
        }
        if (!$setmirror) {
            $this->_validateError(PEAR_CHANNELFILE_ERROR_MIRROR_NOT_FOUND,
                array('mirror' => $mirror));
            return false;
        }
        $set = array('attribs' => array('version' => $version), '_content' => $name);
        if (!isset($setmirror[$type]['function'])) {
            $setmirror[$type]['function'] = $set;
            $this->_xml = null;
            return true;
        } elseif (!isset($setmirror[$type]['function'][0])) {
            $setmirror[$type]['function'] = array($setmirror[$type]['function']);
        }
        $setmirror[$type]['function'][] = $set;
        $this->_xml = null;
        return true;
    }

    /**
     * @param string Resource Type this url links to
     * @param string URL
     * @param string|false mirror name, if this is not a primary server REST base URL
     */
    function setBaseURL($resourceType, $url, $mirror = false)
    {
        if ($mirror) {
            if (!isset($this->_channelInfo['servers']['mirror'])) {
                $this->_validateError(PEAR_CHANNELFILE_ERROR_MIRROR_NOT_FOUND,
                    array('mirror' => $mirror));
                return false;
            }
            $setmirror = false;
            if (isset($this->_channelInfo['servers']['mirror'][0])) {
                foreach ($this->_channelInfo['servers']['mirror'] as $i => $mir) {
                    if ($mirror == $mir['attribs']['host']) {
                        $setmirror = &$this->_channelInfo['servers']['mirror'][$i];
                        break;
                    }
                }
            } else {
                if ($this->_channelInfo['servers']['mirror']['attribs']['host'] == $mirror) {
                    $setmirror = &$this->_channelInfo['servers']['mirror'];
                }
            }
        } else {
            $setmirror = &$this->_channelInfo['servers']['primary'];
        }
        $set = array('attribs' => array('type' => $resourceType), '_content' => $url);
        if (!isset($setmirror['rest'])) {
            $setmirror['rest'] = array();
        }
        if (!isset($setmirror['rest']['baseurl'])) {
            $setmirror['rest']['baseurl'] = $set;
            $this->_xml = null;
            return true;
        } elseif (!isset($setmirror['rest']['baseurl'][0])) {
            $setmirror['rest']['baseurl'] = array($setmirror['rest']['baseurl']);
        }
        foreach ($setmirror['rest']['baseurl'] as $i => $url) {
            if ($url['attribs']['type'] == $resourceType) {
                $this->_xml = null;
                $setmirror['rest']['baseurl'][$i] = $set;
                return true;
            }
        }
        $setmirror['rest']['baseurl'][] = $set;
        $this->_xml = null;
        return true;
    }

    /**
     * @param string mirror server
     * @param int mirror http port
     * @return boolean
     */
    function addMirror($server, $port = null)
    {
        if ($this->_channelInfo['name'] == '__uri') {
            return false; // the __uri channel cannot have mirrors by definition
        }
        $set = array('attribs' => array('host' => $server));
        if (is_numeric($port)) {
            $set['attribs']['port'] = $port;
        }
        if (!isset($this->_channelInfo['servers']['mirror'])) {
            $this->_channelInfo['servers']['mirror'] = $set;
            return true;
        } else {
            if (!isset($this->_channelInfo['servers']['mirror'][0])) {
                $this->_channelInfo['servers']['mirror'] =
                    array($this->_channelInfo['servers']['mirror']);
            }
        }
        $this->_channelInfo['servers']['mirror'][] = $set;
        return true;
    }

    /**
     * Retrieve the name of the validation package for this channel
     * @return string|false
     */
    function getValidationPackage()
    {
        if (!$this->_isValid && !$this->validate()) {
            return false;
        }
        if (!isset($this->_channelInfo['validatepackage'])) {
            return array('attribs' => array('version' => 'default'),
                '_content' => 'PEAR2_Pyrus_Validate');
        }
        return $this->_channelInfo['validatepackage'];
    }

    /**
     * Retrieve the object that can be used for custom validation
     * @param string|false the name of the package to validate.  If the package is
     *                     the channel validation package, PEAR_Validate is returned
     * @return PEAR_Validate|false false is returned if the validation package
     *         cannot be located
     */
    function getValidationObject($package = false)
    {
        if (!$this->_isValid) {
            if (!$this->validate()) {
                return false;
            }
        }
        if (isset($this->_channelInfo['validatepackage'])) {
            if ($package == $this->_channelInfo['validatepackage']) {
                // channel validation packages are always validated by PEAR_Validate
                $val = new PEAR2_Pyrus_Validate;
                return $val;
            }
            if (!class_exists(str_replace('.', '_',
                  $this->_channelInfo['validatepackage']['_content']), true)) {
                return false;
            } else {
                $vclass = str_replace('.', '_',
                    $this->_channelInfo['validatepackage']['_content']);
                $val = new $vclass;
            }
        } else {
            $val = new PEAR2_Pyrus_Validate;
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
        if (isset($this->_channelInfo['_lastmodified'])) {
            return $this->_channelInfo['_lastmodified'];
        }
        return time();
    }
}
?>
