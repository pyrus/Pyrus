<?php
abstract class PEAR2_Pyrus_ChannelRegistry_Base implements PEAR2_Pyrus_IChannelRegistry
{
    /**
     * Parse a package name, or validate a parsed package name array
     * @param string|array pass in an array of format
     *                     array(
     *                      'package' => 'pname',
     *                     ['channel' => 'channame',]
     *                     ['version' => 'version',]
     *                     ['state' => 'state',]
     *                     ['group' => 'groupname'])
     *                     or a string of format
     *                     [channel://][channame/]pname[-version|-state][/group=groupname]
     * @return array|PEAR_Error
     */
    public function parseName($param, $defaultchannel = 'pear.php.net')
    {
        $saveparam = $param;
        if (is_array($param)) {
            // convert to string for error messages
            $saveparam = self::parsedPackageNameToString($param);
            // process the array
            if (!isset($param['package'])) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('parsePackageName(): array $param ' .
                    'must contain a valid package name in index "param"');
            }
            if (!isset($param['uri'])) {
                if (!isset($param['channel'])) {
                    $param['channel'] = $defaultchannel;
                }
            } else {
                $param['channel'] = '__uri';
            }
        } else {
            $components = @parse_url((string) $param);
            if (isset($components['scheme'])) {
                if ($components['scheme'] == 'http') {
                    // uri package
                    $param = array('uri' => $param, 'channel' => '__uri');
                } elseif($components['scheme'] != 'channel') {
                    throw new PEAR2_Pyrus_ChannelRegistry_Exception('parsePackageName(): only channel:// uris may ' .
                        'be downloaded, not "' . $param . '"');//, 'invalid');
                }
            }
            if (!isset($components['path'])) {
                throw new PEAR2_Registry_Exception('parsePackageName(): array $param ' .
                    'must contain a valid package name in "' . $param . '"');
            }
            if (isset($components['host'])) {
                // remove the leading "/"
                $components['path'] = substr($components['path'], 1);
            }
            if (!isset($components['scheme'])) {
                if (strpos($components['path'], '/') !== false) {
                    if ($components['path']{0} == '/') {
                        throw new PEAR2_Pyrus_ChannelRegistry_Exception('parsePackageName(): this is not ' .
                            'a package name, it begins with "/" in "' . $param . '"');
                    }
                    $parts = explode('/', $components['path']);
                    $components['host'] = array_shift($parts);
                    if (count($parts) > 1) {
                        $components['path'] = array_pop($parts);
                        $components['host'] .= '/' . implode('/', $parts);
                    } else {
                        $components['path'] = implode('/', $parts);
                    }
                } else {
                    $components['host'] = $defaultchannel;
                }
            } else {
                if (strpos($components['path'], '/')) {
                    $parts = explode('/', $components['path']);
                    $components['path'] = array_pop($parts);
                    $components['host'] .= '/' . implode('/', $parts);
                }
            }

            if (is_array($param)) {
                $param['package'] = $components['path'];
            } else {
                $param = array(
                    'package' => $components['path']
                    );
                if (isset($components['host'])) {
                    $param['channel'] = $components['host'];
                }
            }
            if (isset($components['fragment'])) {
                $param['group'] = $components['fragment'];
            }
            if (isset($components['user'])) {
                $param['user'] = $components['user'];
            }
            if (isset($components['pass'])) {
                $param['pass'] = $components['pass'];
            }
            if (isset($components['query'])) {
                parse_str($components['query'], $param['opts']);
            }
            // check for extension
            $pathinfo = pathinfo($param['package']);
            if (isset($pathinfo['extension']) &&
                  in_array(strtolower($pathinfo['extension']), array('tgz', 'tar'))) {
                $param['extension'] = $pathinfo['extension'];
                $param['package'] = substr($pathinfo['basename'], 0,
                    strlen($pathinfo['basename']) - 4);
            }
            // check for version
            if (strpos($param['package'], '-')) {
                $test = explode('-', $param['package']);
                if (count($test) != 2) {
                    throw new PEAR2_Pyrus_ChannelRegistry_Exception('parsePackageName(): only one version/state ' .
                        'delimiter "-" is allowed in "' . $saveparam . '"');
                }
                list($param['package'], $param['version']) = $test;
            }
        }
        // validation
        $info = $this->exists($param['channel']);
        if (!$info) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('unknown channel "' . $param['channel'] .
                '" in "' . $saveparam . '"', 'channel');
        }
        $chan = $this->getObject($param['channel']);
        if (!$chan) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception("Exception: corrupt registry, could not " .
                "retrieve channel " . $param['channel'] . " information");
        }
        $param['channel'] = $chan->getName();
        $validate = $chan->getValidationObject();
        $vpackage = $chan->getValidationPackage();
        // validate package name
        if (!$validate->validPackageName($param['package'], $vpackage['_content'])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('parsePackageName(): invalid package name "' .
                $param['package'] . '" in "' . $saveparam . '"');
        }
        if (isset($param['group'])) {
            if (!PEAR2_Pyrus_Validate::validGroupName($param['group'])) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('parsePackageName(): dependency group "' . $param['group'] .
                    '" is not a valid group name in "' . $saveparam . '"');
            }
        }
        if (isset($param['state'])) {
            if (!in_array(strtolower($param['state']), $validate->getValidStates())) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('parsePackageName(): state "' . $param['state']
                    . '" is not a valid state in "' . $saveparam . '"');
            }
        }
        if (isset($param['version'])) {
            if (isset($param['state'])) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('parsePackageName(): cannot contain both ' .
                    'a version and a stability (state) in "' . $saveparam . '"',
                    'version/state');
            }
            // check whether version is actually a state
            if (in_array(strtolower($param['version']), $validate->getValidStates())) {
                $param['state'] = strtolower($param['version']);
                unset($param['version']);
            } else {
                if (!$validate->validVersion($param['version'])) {
                    throw new PEAR2_Pyrus_ChannelRegistry_Exception('parsePackageName(): "' . $param['version'] .
                        '" is neither a valid version nor a valid state in "' .
                        $saveparam . '"');
                }                    
            }
        }
        return $param;
    }

    /**
     * @param array
     * @return string
     */
    function parsedNameToString($parsed, $brief = false)
    {
        if (is_string($parsed)) {
            return $parsed;
        }
        if (is_object($parsed)) {
            $p = $parsed;
            $parsed = array(
                'package' => $p->getPackage(),
                'channel' => $p->getChannel(),
                'version' => $p->getVersion(),
            );
        }
        if (isset($parsed['uri'])) {
            return $parsed['uri'];
        }
        if ($brief) {
            if ($channel = $this->getAlias($parsed['channel'])) {
                return $channel . '/' . $parsed['package'];
            }
        }
        $upass = '';
        if (isset($parsed['user'])) {
            $upass = $parsed['user'];
            if (isset($parsed['pass'])) {
                $upass .= ':' . $parsed['pass'];
            }
            $upass = "$upass@";
        }
        $ret = 'channel://' . $upass . $parsed['channel'] . '/' . $parsed['package'];
        if (isset($parsed['version']) || isset($parsed['state'])) {
            $ver = isset($parsed['version']) ? $parsed['version'] : '';
            $ver .= isset($parsed['state']) ? $parsed['state'] : '';
            $ret .= '-' . $ver;
        }
        if (isset($parsed['extension'])) {
            $ret .= '.' . $parsed['extension'];
        }
        if (isset($parsed['opts'])) {
            $ret .= '?';
            foreach ($parsed['opts'] as $name => $value) {
                $parsed['opts'][$name] = "$name=$value";
            }
            $ret .= implode('&', $parsed['opts']);
        }
        if (isset($parsed['group'])) {
            $ret .= '#' . $parsed['group'];
        }
        return $ret;
    }
}