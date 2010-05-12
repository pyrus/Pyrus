<?php
/**
 * PEAR2\Pyrus\Registry\Pear1\DependencyDB, advanced installed packages
 * dependency database for Pear1 registry
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
 * Track dependency relationships between installed packages
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Tomas V.V.Cox <cox@idec.net.com>
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus\Registry\Pear1;
class DependencyDB
{
    /**
     * Filename of the dependency DB (usually .depdb)
     * @var string
     * @access private
     */
    var $_depdb = false;
    /**
     * File name of the lockfile (usually .depdblock)
     * @var string
     * @access private
     */
    var $_lockfile = false;
    /**
     * Open file resource for locking the lockfile
     * @var resource|false
     * @access private
     */
    var $_lockFp = false;
    /**
     * API version of this class, used to validate a file on-disk
     * @var string
     * @access private
     */
    var $_version = '1.0';

    function __construct($path)
    {
        $this->_depdb = $path . DIRECTORY_SEPARATOR . '.depdb';

        $this->_lockfile = dirname($this->_depdb) . DIRECTORY_SEPARATOR . '.depdblock';
    }

    function hasWriteAccess()
    {
        if (!file_exists($this->_depdb)) {
            $dir = $this->_depdb;
            while ($dir && $dir != '.') {
                $dir = dirname($dir); // cd ..
                if ($dir != '.' && file_exists($dir)) {
                    if (is_writeable($dir)) {
                        return true;
                    }
                    return false;
                }
            }
            return false;
        }
        return is_writeable($this->_depdb);
    }

    /**
     * Get a list of installed packages that depend on this package
     * @return array
     */
    function getDependentPackages(\PEAR2\Pyrus\PackageFileInterface $pkg)
    {
        $data = $this->_getDepDB();

        if (isset($data['packages']) && isset($data['packages'][$pkg->channel][strtolower($pkg->name)])) {
            return $data['packages'][$pkg->channel][strtolower($pkg->name)];
        }
        return array();
    }

    /**
     * Register dependencies of a package that is being installed or upgraded
     */
    function installPackage(\PEAR2\Pyrus\PackageFileInterface $package)
    {
        $data = $this->_getDepDB();
        $data = $this->_setPackageDeps($data, $package);
        $this->_writeDepDB($data);
    }

    /**
     * Remove dependencies of a package that is being uninstalled, or upgraded.
     *
     * Upgraded packages first uninstall, then install
     */
    function uninstallPackage($channel, $package)
    {
        $package = strtolower($package);
        $data = $this->_getDepDB();

        if (!isset($data['dependencies'][$channel][$package])) {
            return true;
        }

        foreach ($data['dependencies'][$channel][$package] as $dep) {
            $found      = false;
            $depchannel = isset($dep['dep']['uri']) ? '__uri' : strtolower($dep['dep']['channel']);
            $depname    = strtolower($dep['dep']['name']);
            if (isset($data['packages'][$depchannel][$depname])) {
                foreach ($data['packages'][$depchannel][$depname] as $i => $info) {
                    if ($info['channel'] == $channel && $info['package'] == $package) {
                        $found = true;
                        break;
                    }
                }
            }

            if ($found) {
                unset($data['packages'][$depchannel][$depname][$i]);
                if (!count($data['packages'][$depchannel][$depname])) {
                    unset($data['packages'][$depchannel][$depname]);
                    if (!count($data['packages'][$depchannel])) {
                        unset($data['packages'][$depchannel]);
                    }
                } else {
                    $data['packages'][$depchannel][$depname] =
                        array_values($data['packages'][$depchannel][$depname]);
                }
            }
        }

        unset($data['dependencies'][$channel][$package]);
        if (!count($data['dependencies'][$channel])) {
            unset($data['dependencies'][$channel]);
        }

        if (!count($data['dependencies'])) {
            unset($data['dependencies']);
        }

        if (!count($data['packages'])) {
            unset($data['packages']);
        }

        $this->_writeDepDB($data);
    }

    /**
     * Rebuild the dependency DB by reading registry entries.
     */
    function rebuildDB()
    {
        $depdb = array('_version' => $this->_version);
        if (!$this->hasWriteAccess()) {
            // allow startup for read-only with older Registry
            return $depdb;
        }
        $reg = \PEAR2\Pyrus\Config::current()->registry;

        foreach (\PEAR2\Pyrus\Config::current()->channelregistry as $channel) {
            foreach ($reg->listPackages($channel->name) as $package) {
                $package = $reg->package[$channel->name . '/' . $package];
                $depdb = $this->_setPackageDeps($depdb, $package);
            }
        }

        $this->_writeDepDB($depdb);
    }

    /**
     * Register usage of the dependency DB to prevent race conditions
     * @param int one of the LOCK_* constants
     */
    private function _lock($mode = LOCK_EX)
    {
        if (stristr(php_uname(), 'Windows 9')) {
            return;
        }

        if ($mode != LOCK_UN && is_resource($this->_lockFp)) {
            // XXX does not check type of lock (LOCK_SH/LOCK_EX)
            return;
        }

        $open_mode = 'w';
        // XXX People reported problems with LOCK_SH and 'w'
        if ($mode === LOCK_SH) {
            if (!file_exists($this->_lockfile)) {
                touch($this->_lockfile);
            } elseif (!is_file($this->_lockfile)) {
                throw new \PEAR2\Pyrus\Registry\Exception('could not create Dependency lock file, ' .
                    'it exists and is not a regular file');
            }
            $open_mode = 'r';
        }

        if (!is_resource($this->_lockFp)) {
            $this->_lockFp = @fopen($this->_lockfile, $open_mode);
        }

        if (!is_resource($this->_lockFp)) {
            throw new \PEAR2\Pyrus\Registry\Exception("could not create Dependency lock file" .
                                     (isset($php_errormsg) ? ": " . $php_errormsg : ""));
        }

        if (!(int)flock($this->_lockFp, $mode)) {
            switch ($mode) {
                case LOCK_SH: $str = 'shared';    break;
                case LOCK_EX: $str = 'exclusive'; break;
                case LOCK_UN: $str = 'unlock';    break;
                default:      $str = 'unknown';   break;
            }

            throw new \PEAR2\Pyrus\Registry\Exception("could not acquire $str lock ($this->_lockfile)");
        }
    }

    /**
     * Release usage of dependency DB
     * @access private
     */
    function _unlock()
    {
        $this->_lock(LOCK_UN);
        if (is_resource($this->_lockFp)) {
            fclose($this->_lockFp);
        }
        $this->_lockFp = null;
    }

    /**
     * Load the dependency database from disk
     * @return array
     */
    function _getDepDB()
    {
        if (!$this->hasWriteAccess()) {
            return array('_version' => $this->_version);
        }

        if (!file_exists($this->_depdb)) {
            return array('_version' => $this->_version);
        }

        if (!$fp = fopen($this->_depdb, 'r')) {
            throw new \PEAR2\Pyrus\Registry\Exception("Could not open dependencies file `" .
                                                     $this->_depdb . "'");
        }

        $rt = get_magic_quotes_runtime();
        if ($rt) {
            @set_magic_quotes_runtime(0);
        }

        clearstatcache();
        fclose($fp);
        $data = unserialize(file_get_contents($this->_depdb));

        if ($rt) {
            @set_magic_quotes_runtime($rt);
        }
        return $data;
    }

    /**
     * Write out the dependency database to disk
     * @param array the database
     * @return void
     * @throws \PEAR2\Pyrus\Registry\Exception
     * @access private
     */
    function _writeDepDB($deps)
    {
        $this->_lock(LOCK_EX);

        if (!$fp = fopen($this->_depdb, 'wb')) {
            $this->_unlock();
            throw new \PEAR2\Pyrus\Registry\Exception("Could not open dependencies file `" .
                                                     $this->_depdb . "' for writing");
        }

        $rt = get_magic_quotes_runtime();
        if ($rt) {
            @set_magic_quotes_runtime(0);
        }
        fwrite($fp, serialize($deps));
        if ($rt) {
            @set_magic_quotes_runtime($rt);
        }
        fclose($fp);
        $this->_unlock();
    }

    /**
     * Register all dependencies from a package in the dependencies database, in essence
     * "installing" the package's dependency information
     * @param array the database
     * @param \PEAR2\Pyrus\PackageFileInterface
     * @access private
     */
    function _setPackageDeps(array $data, \PEAR2\Pyrus\PackageFileInterface $pkg)
    {
        $deps = $pkg->rawdeps;

        if (!$deps) {
            return;
        }

        if (!is_array($data)) {
            $data = array();
        }

        if (!isset($data['dependencies'])) {
            $data['dependencies'] = array();
        }

        $channel = $pkg->channel;
        $package = strtolower($pkg->name);

        if (!isset($data['dependencies'][$channel])) {
            $data['dependencies'][$channel] = array();
        }

        $data['dependencies'][$channel][$package] = array();
        if (isset($deps['required']['package'])) {
            if (!isset($deps['required']['package'][0])) {
                $deps['required']['package'] = array($deps['required']['package']);
            }

            foreach ($deps['required']['package'] as $dep) {
                $data = $this->_registerDep($data, $pkg, $dep, 'required');
            }
        }

        if (isset($deps['optional']['package'])) {
            if (!isset($deps['optional']['package'][0])) {
                $deps['optional']['package'] = array($deps['optional']['package']);
            }

            foreach ($deps['optional']['package'] as $dep) {
                $data = $this->_registerDep($data, $pkg, $dep, 'optional');
            }
        }

        if (isset($deps['required']['subpackage'])) {
            if (!isset($deps['required']['subpackage'][0])) {
                $deps['required']['subpackage'] = array($deps['required']['subpackage']);
            }

            foreach ($deps['required']['subpackage'] as $dep) {
                $data = $this->_registerDep($data, $pkg, $dep, 'required');
            }
        }

        if (isset($deps['optional']['subpackage'])) {
            if (!isset($deps['optional']['subpackage'][0])) {
                $deps['optional']['subpackage'] = array($deps['optional']['subpackage']);
            }

            foreach ($deps['optional']['subpackage'] as $dep) {
                $data = $this->_registerDep($data, $pkg, $dep, 'optional');
            }
        }

        if (isset($deps['group'])) {
            if (!isset($deps['group'][0])) {
                $deps['group'] = array($deps['group']);
            }

            foreach ($deps['group'] as $group) {
                if (isset($group['package'])) {
                    if (!isset($group['package'][0])) {
                        $group['package'] = array($group['package']);
                    }

                    foreach ($group['package'] as $dep) {
                        $data = $this->_registerDep($data, $pkg, $dep, 'optional',
                            $group['attribs']['name']);
                    }
                }

                if (isset($group['subpackage'])) {
                    if (!isset($group['subpackage'][0])) {
                        $group['subpackage'] = array($group['subpackage']);
                    }

                    foreach ($group['subpackage'] as $dep) {
                        $data = $this->_registerDep($data, $pkg, $dep, 'optional',
                            $group['attribs']['name']);
                    }
                }
            }
        }

        if ($data['dependencies'][$channel][$package] == array()) {
            unset($data['dependencies'][$channel][$package]);
            if (!count($data['dependencies'][$channel])) {
                unset($data['dependencies'][$channel]);
            }
        }
        return $data;
    }

    /**
     * @param array the database
     * @param \PEAR2\Pyrus\PackageFileInterface
     * @param array the specific dependency
     * @param required|optional whether this is a required or an optional dep
     * @param string|false dependency group this dependency is from, or false for ordinary dep
     */
    function _registerDep(array $data, \PEAR2\Pyrus\PackageFileInterface $pkg, $dep, $type, $group = false)
    {
        $info = array(
            'dep'   => $dep,
            'type'  => $type,
            'group' => $group
        );

        if (isset($dep['name'])) {
            $dep['name'] = strtolower($dep['name']);
        }
        $depchannel = isset($dep['channel']) ? $dep['channel'] : '__uri';
        if (!isset($data['dependencies'])) {
            $data['dependencies'] = array();
        }

        $channel = $pkg->channel;
        $package = strtolower($pkg->name);

        if (!isset($data['dependencies'][$channel])) {
            $data['dependencies'][$channel] = array();
        }

        if (!isset($data['dependencies'][$channel][$package])) {
            $data['dependencies'][$channel][$package] = array();
        }

        $data['dependencies'][$channel][$package][] = $info;
        if (isset($data['packages'][$depchannel][$dep['name']])) {
            $found = false;
            foreach ($data['packages'][$depchannel][$dep['name']] as $i => $p) {
                if ($p['channel'] == $channel && $p['package'] == $package) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $data['packages'][$depchannel][$dep['name']][] = array(
                    'channel' => $channel,
                    'package' => $package
                );
            }
        } else {
            if (!isset($data['packages'])) {
                $data['packages'] = array();
            }

            if (!isset($data['packages'][$depchannel])) {
                $data['packages'][$depchannel] = array();
            }

            if (!isset($data['packages'][$depchannel][$dep['name']])) {
                $data['packages'][$depchannel][$dep['name']] = array();
            }

            $data['packages'][$depchannel][$dep['name']][] = array(
                'channel' => $channel,
                'package' => $package
            );
        }
        return $data;
    }
}