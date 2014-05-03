<?php
/**
 * \Pyrus\ChannelRegistry\Base
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

namespace Pyrus\ChannelRegistry;

/**
 * Base class for Pyrus managed channel registries
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
abstract class Base implements \Pyrus\ChannelRegistryInterface, \Iterator
{
    protected $path;
    protected $readonly;
    protected $initialized;

    protected function lazyInit()
    {
        // lazy initialization
        if (!$this->initialized && 1 === $this->exists('pear.php.net')) {
            $this->initialized = true;
            $this->initDefaultChannels();
        } else {
            $this->initialized = true;
        }
    }

    /**
     * Parse a package name, or validate a parsed package name array
     * @param string string of format
     *               [channel://][channame/]pname[-version|-state][/group=groupname]
     *               [http|https]://uri
     *
     * @return array
     */
    public function parseName($param, $defaultchannel = 'pear2.php.net')
    {
        $saveparam = $param;
        $components = @parse_url((string) $param);
        if (isset($components['scheme'])) {
            if ($components['scheme'] == 'http' || $components['scheme'] == 'https') {
                // uri package
                $param = array('uri' => $param, 'channel' => '__uri');
                return $param;
            } elseif($components['scheme'] != 'channel') {
                throw new ParseException('parsePackageName(): only channel:// uris may ' .
                    'be downloaded, not "' . $param . '"', 'scheme');
            }
        }

        if (!isset($components['path'])) {
            throw new ParseException('parsePackageName(): array $param ' .
                'must contain a valid package name in "' . $param . '"', 'path');
        }

        if (isset($components['host'])) {
            // remove the leading "/"
            $components['path'] = substr($components['path'], 1);
        }

        if (!isset($components['scheme'])) {
            if (strpos($components['path'], '/') !== false) {
                if ($components['path']{0} == '/') {
                    throw new ParseException('parsePackageName(): this is not ' .
                        'a package name, it begins with "/" in "' . $param . '"', 'invalid');
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

        $param = array(
            'package' => $components['path']
            );
        if (isset($components['host'])) {
            $param['channel'] = $components['host'];
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
        $exts = array('tgz', 'tar', 'zip', 'tbz', 'phar');
        if (isset($pathinfo['extension']) && in_array(strtolower($pathinfo['extension']), $exts)) {
            $param['extension'] = $pathinfo['extension'];
            $param['package'] = substr($pathinfo['basename'], 0,
                strlen($pathinfo['basename']) - strlen($pathinfo['extension']) - 1);
        }

        // check for version
        if (strpos($param['package'], '-')) {
            $test = explode('-', $param['package']);
            if (count($test) != 2) {
                throw new ParseException('parseName(): only one version/state ' .
                    'delimiter "-" is allowed in "' . $saveparam . '"', 'invalid');
            }

            list($param['package'], $param['version']) = $test;
        }

        // validation
        $info = $this->exists($param['channel'], false);
        if (!$info) {
            throw new ParseException('unknown channel "' . $param['channel'] .
                '" in "' . $saveparam . '"', 'channel', $param);
        }

        try {
            $chan = $this->get($param['channel'], false);
        } catch (\Exception $e) {
            throw new ParseException("Exception: corrupt registry, could not " .
                "retrieve channel " . $param['channel'] . " information", 'other', $e);
        }

        $param['channel'] = $chan->name;
        $validate = $chan->getValidationObject(false);
        $vpackage = $chan->getValidationPackage();
        // validate package name
        if (!$validate->validPackageName($param['package'], $vpackage['_content'])) {
            throw new ParseException('parseName(): invalid package name "' .
                $param['package'] . '" in "' . $saveparam . '"', 'package');
        }

        if (isset($param['group'])) {
            if (!\Pyrus\Validate::validGroupName($param['group'])) {
                throw new ParseException('parseName(): dependency group "' . $param['group'] .
                    '" is not a valid group name in "' . $saveparam . '"', 'group');
            }
        }

        if (isset($param['version'])) {
            // check whether version is actually a state
            if (in_array(strtolower($param['version']), $validate->getValidStates())) {
                $param['state'] = strtolower($param['version']);
                unset($param['version']);
            } else {
                if (!$validate->validVersion($param['version'])) {
                    throw new ParseException('parseName(): "' . $param['version'] .
                        '" is neither a valid version nor a valid state in "' .
                        $saveparam . '"', 'version/state');
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
            if ($p->channel == '__uri') {
                $parsed = array(
                    'uri' => $p->uri,
                );
            } else {
                $parsed = array(
                    'package' => $p->name,
                    'channel' => $p->channel,
                    'version' => $p->version['release'],
                );
            }
        }

        if (isset($parsed['uri'])) {
            return $parsed['uri'];
        }

        if ($brief) {
            if ($channel = $this->get($parsed['channel'])->alias) {
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
                $parsed['opts'][$name] = urlencode($name) . '=' . urlencode($value);
            }

            $ret .= implode('&', $parsed['opts']);
        }

        if (isset($parsed['group'])) {
            $ret .= '#' . $parsed['group'];
        }

        return $ret;
    }

    function current()
    {
        return $this->get(current($this->channelList));
    }

    function key()
    {
        return current($this->channelList);
    }

    function valid()
    {
        return current($this->channelList);
    }

    function next()
    {
        return next($this->channelList);
    }

    function rewind()
    {
        $this->channelList = $this->listChannels();
    }

    public function getPyrusChannel()
    {
        return $this->getDefaultChannel('pyrus.net');
    }

    public function getPearChannel()
    {
        return $this->getDefaultChannel('pear.php.net');
    }

    public function getPear2Channel()
    {
        return $this->getDefaultChannel('pear2.php.net');
    }

    public function getPeclChannel()
    {
        return $this->getDefaultChannel('pecl.php.net');
    }

    public function getDocChannel()
    {
        return $this->getDefaultChannel('doc.php.net');
    }

    public function getUriChannel()
    {
        return $this->getDefaultChannel('__uri');
    }

    protected function getDefaultChannel($channel)
    {
        $xml = \Pyrus\Main::getDataPath() . '/default_channels/' . $channel . '.xml';
        $parser = new \Pyrus\ChannelFile\Parser\v1;
        $info = $parser->parse($xml);
        return new Channel($this, $info->getArray());
    }

    /**
     * Set up default channels, for uninitialized channel registries
     */
    protected function initDefaultChannels()
    {
        $pyrus = $this->getPyrusChannel();
        $pear = $this->getPearChannel();
        $pear2 = $this->getPear2Channel();
        $pecl = $this->getPeclChannel();
        $__uri = $this->getUriChannel();
        $doc = $this->getDocChannel();
        $this->add($pyrus);
        $this->add($pear);
        $this->add($pear2);
        $this->add($pecl);
        $this->add($doc);
        $this->add($__uri);
    }

    function exists($channel, $strict = true)
    {
        if (in_array($channel, $this->getDefaultChannels())) {
            return 1;
        }

        if (!$strict && in_array($channel, $this->getDefaultChannelAliases())) {
            return 1;
        }

        return false;
    }

    function channelFromAlias($alias)
    {
        $aliases = $this->getDefaultChannelAliases();
        $channels = $this->getDefaultChannels();
        if (in_array($alias, $aliases)) {
            $aliases = array_flip($aliases);
            return $channels[$aliases[$alias]];
        }

        if (in_array($alias, $channels)) {
            return $alias;
        }

        throw new \Pyrus\ChannelFile\Exception('Unknown channel/alias: ' . $alias);
    }

    function getDefaultChannels()
    {
        return array('__uri', 'pear2.php.net', 'pear.php.net', 'pecl.php.net', 'doc.php.net', 'pyrus.net');
    }

    function getDefaultChannelAliases()
    {
        return array('__uri', 'pear2', 'pear', 'pecl', 'phpdocs', 'pyrus');
    }

    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string
     * @return int
     */
    function packageCount($channel)
    {
        return count($this->getRegistry()->listPackages($channel));
    }

    /**
     * @return \Pyrus\RegistryInterface
     */
    function getRegistry()
    {
        $class = str_replace('Channel', '', get_class($this));
        $ret = new $class($this->path, $this->readonly);
        return $ret;
    }
}
