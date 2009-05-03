<?php
/**
 * PEAR2_Pyrus_Dependency_Validator, advanced dependency validation
 *
 * PHP versions 4 and 5
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
 * Dependency check for PEAR2 packages
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Dependency_Validator
{
    /**
     * @var PEAR2_MultiErrors
     */
    protected $errs;
    /**
     * One of the PEAR2_Pyrus_Validate::* states
     * @see PEAR2_Pyrus_Validate::NORMAL
     * @var integer
     */
    var $_state;
    /**
     * @var PEAR2_Pyrus_OSGuess
     */
    var $_os;
    /**
     * Package to validate
     * @var PEAR2_Pyrus_Package
     */
    var $_currentPackage;
    /**
     * @param PEAR2_Pyrus_Package
     * @param int installation state (one of PEAR2_Pyrus_Validate::*)
     * @param PEAR2_MultiErrors
     */
    function __construct($package, $state = PEAR2_Pyrus_Validate::INSTALLING,
                         PEAR2_MultiErrors $errs)
    {
        $this->_state = $state;
        $this->_currentPackage = $package;
        $this->errs = $errs;
    }

    function _getExtraString($dep)
    {
        $extra = ' (';
        if ($dep->type != 'extension' && isset($dep->uri)) {
            return '';
        }
        if (isset($dep->recommended)) {
            $extra .= 'recommended version ' . $dep->recommended;
        } else {
            if (isset($dep->min)) {
                $extra .= 'version >= ' . $dep->min;
            }
            if (isset($dep->max)) {
                if ($extra != ' (') {
                    $extra .= ', ';
                }
                $extra .= 'version <= ' . $dep->max;
            }
            if (isset($dep->exclude)) {
                if (!is_array($dep->exclude)) {
                    $dep->exclude = array($dep->exclude);
                }
                if ($extra != ' (') {
                    $extra .= ', ';
                }
                $extra .= 'excluded versions: ';
                foreach ($dep->exclude as $i => $exclude) {
                    if ($i) {
                        $extra .= ', ';
                    }
                    $extra .= $exclude;
                }
            }
        }
        $extra .= ')';
        if ($extra == ' ()') {
            $extra = '';
        }
        return $extra;
    }

    /**
     * This makes unit-testing a heck of a lot easier
     */
    function getPHP_OS()
    {
        return PHP_OS;
    }

    /**
     * This makes unit-testing a heck of a lot easier
     */
    function getsysname()
    {
        $this->_os = new PEAR2_Pyrus_OSGuess;
        return $this->_os->getSysname();
    }

    /**
     * Specify a dependency on an OS.  Use arch for detailed os/processor information
     *
     * There are two generic OS dependencies that will be the most common, unix and windows.
     * Other options are linux, freebsd, darwin (OS X), sunos, irix, hpux, aix
     */
    function validateOsDependency(PEAR2_Pyrus_PackageFile_v2_Dependencies_Dep $dep)
    {
        if ($this->_state != PEAR2_Pyrus_Validate::INSTALLING &&
              $this->_state != PEAR2_Pyrus_Validate::DOWNLOADING) {
            return true;
        }
        if ($dep->name == '*') {
            return true; // no one will do conflicts with *, so assume no conflicts
        }
        switch (strtolower($dep->name)) {
            case 'windows' :
                if ($dep->conflicts) {
                    if (strtolower(substr($this->getPHP_OS(), 0, 3)) == 'win') {
                        if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) &&
                              !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                            return $this->raiseError("Cannot install %s on Windows");
                        } else {
                            return $this->warning("warning: Cannot install %s on Windows");
                        }
                    }
                } else {
                    if (strtolower(substr($this->getPHP_OS(), 0, 3)) != 'win') {
                        if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) &&
                              !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                            return $this->raiseError("Can only install %s on Windows");
                        } else {
                            return $this->warning("warning: Can only install %s on Windows");
                        }
                    }
                }
            break;
            case 'unix' :
                $unices = array('linux', 'freebsd', 'darwin', 'sunos', 'irix', 'hpux', 'aix');
                if ($dep->conflicts) {
                    if (in_array(strtolower($this->getSysname()), $unices)) {
                        if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) &&
                              !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                            return $this->raiseError("Cannot install %s on any Unix system");
                        } else {
                            return $this->warning(
                                "warning: Cannot install %s on any Unix system");
                        }
                    }
                } else {
                    if (!in_array(strtolower($this->getSysname()), $unices)) {
                        if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) &&
                              !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                            return $this->raiseError("Can only install %s on a Unix system");
                        } else {
                            return $this->warning(
                                "warning: Can only install %s on a Unix system");
                        }
                    }
                }
            break;
            default :
                if ($dep->conflicts) {
                    if (strtolower($dep->name) == strtolower($this->getSysname())) {
                        if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) &&
                              !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                            return $this->raiseError('Cannot install %s on ' . $dep->name .
                                ' operating system');
                        } else {
                            return $this->warning('warning: Cannot install %s on ' .
                                $dep->name . ' operating system');
                        }
                    }
                } else {
                    if (strtolower($dep->name) != strtolower($this->getSysname())) {
                        if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) &&
                              !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                            return $this->raiseError('Cannot install %s on ' .
                                $this->getSysname() .
                                ' operating system, can only install on ' . $dep->name);
                        } else {
                            return $this->warning('warning: Cannot install %s on ' .
                                $this->getSysname() .
                                ' operating system, can only install on ' . $dep->name);
                        }
                    }
                }
        }
        return true;
    }

    /**
     * This makes unit-testing a heck of a lot easier
     */
    function matchSignature($pattern)
    {
        $this->_os = new PEAR2_Pyrus_OSGuess;
        return $this->_os->matchSignature($pattern);
    }

    /**
     * Specify a complex dependency on an OS/processor/kernel version,
     * Use OS for simple operating system dependency.
     *
     * This is the only dependency that accepts an eregable pattern.  The pattern
     * will be matched against the php_uname() output parsed by OS_Guess
     */
    function validateArchDependency($dep)
    {
        if ($this->_state != PEAR2_Pyrus_Validate::INSTALLING) {
            return true;
        }
        if ($this->matchSignature($dep->pattern)) {
            if ($dep->conflicts) {
                if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                    return $this->raiseError('%s Architecture dependency failed, cannot match "' .
                        $dep->pattern . '"');
                }
                return $this->warning('warning: %s Architecture dependency failed, ' .
                    'cannot match "' . $dep->pattern . '"');
            }
            return true;
        } else {
            if ($dep->conflicts) {
                return true;
            }
            if (!isset(PEAR2_Pyrus_Installer::$options['nodeps'])
                && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                return $this->raiseError('%s Architecture dependency failed, does not ' .
                    'match "' . $dep->pattern . '"');
            }
            return $this->warning('warning: %s Architecture dependency failed, does ' .
                'not match "' . $dep->pattern . '"');
        }
    }

    /**
     * This makes unit-testing a heck of a lot easier
     */
    function extension_loaded($name)
    {
        return extension_loaded($name);
    }

    /**
     * This makes unit-testing a heck of a lot easier
     */
    function phpversion($name = null)
    {
        if ($name !== null) {
            return phpversion($name);
        } else {
            return phpversion();
        }
    }

    function validateExtensionDependency(PEAR2_Pyrus_PackageFile_v2_Dependencies_Package $dep)
    {
        if ($this->_state != PEAR2_Pyrus_Validate::INSTALLING &&
              $this->_state != PEAR2_Pyrus_Validate::DOWNLOADING) {
            return true;
        }
        $required = $dep->deptype == 'required';
        $loaded = $this->extension_loaded($dep->name);
        $extra = $this->_getExtraString($dep);
        if (!isset($dep->min) && !isset($dep->max) &&
              !isset($dep->recommended) && !isset($dep->exclude)) {
            if ($loaded) {
                if ($dep->conflicts) {
                    if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                        return $this->raiseError('%s conflicts with PHP extension "' .
                            $dep->name . '"' . $extra);
                    } else {
                        return $this->warning('warning: %s conflicts with PHP extension "' .
                            $dep->name . '"' . $extra);
                    }
                }
                return true;
            } else {
                if ($dep->conflicts) {
                    return true;
                }
                if ($required) {
                    if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                        return $this->raiseError('%s requires PHP extension "' .
                            $dep->name . '"' . $extra);
                    } else {
                        return $this->warning('warning: %s requires PHP extension "' .
                            $dep->name . '"' . $extra);
                    }
                } else {
                    return $this->warning('%s can optionally use PHP extension "' .
                        $dep->name . '"' . $extra);
                }
            }
        }
        if (!$loaded) {
            if ($dep->conflicts) {
                return true;
            }
            if (!$required) {
                return $this->warning('%s can optionally use PHP extension "' .
                    $dep->name . '"' . $extra);
            } else {
                if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                    return $this->raiseError('%s requires PHP extension "' . $dep->name .
                        '"' . $extra);
                }
                    return $this->warning('warning: %s requires PHP extension "' . $dep->name .
                        '"' . $extra);
            }
        }
        $version = (string) $this->phpversion($dep->name);
        if (empty($version)) {
            $version = '0';
        }
        $fail = false;
        if (isset($dep->min)) {
            if (!version_compare($version, $dep->min, '>=')) {
                $fail = true;
            }
        }
        if (isset($dep->max)) {
            if (!version_compare($version, $dep->max, '<=')) {
                $fail = true;
            }
        }
        if ($fail && !$dep->conflicts) {
            if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                return $this->raiseError('%s requires PHP extension "' . $dep->name .
                    '"' . $extra . ', installed version is ' . $version);
            } else {
                return $this->warning('warning: %s requires PHP extension "' . $dep->name .
                    '"' . $extra . ', installed version is ' . $version);
            }
        } elseif (!isset($dep->exclude) && (isset($dep->min) || isset($dep->max)) && !$fail && $dep->conflicts) {
            if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                return $this->raiseError('%s conflicts with PHP extension "' .
                    $dep->name . '"' . $extra . ', installed version is ' . $version);
            } else {
                return $this->warning('warning: %s conflicts with PHP extension "' .
                    $dep->name . '"' . $extra . ', installed version is ' . $version);
            }
        }
        if (isset($dep->exclude)) {
            // exclude ordinarily tells the installer "install anything but these versions"
            // when paired with conflicts, it becomes "install only these versions"
            $conflicts = $dep->conflicts;
            foreach ($dep->exclude as $exclude) {
                if (version_compare($version, $exclude, '==')) {
                    if ($conflicts) {
                        $fail = false;
                        break;
                    }
                    goto conflict_error;
                } else {
                    if ($conflicts) {
                        $fail = true;
                    }
                }
            }
        }
        if ($fail) {
conflict_error:
            if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                return $this->raiseError('%s is not compatible with version ' . $version . ' of PHP extension "' .
                    $dep->name . '", installed version is ' . $version);
            } else {
                return $this->warning('warning: %s is not compatible with version ' . $version . ' of PHP extension "' .
                    $dep->name . '", installed version is ' . $version);
            }
        }
        if (isset($dep->recommended)) {
            if (version_compare($version, $dep->recommended, '==')) {
                return true;
            } else {
                if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                    return $this->raiseError('%s dependency: PHP extension ' . $dep->name .
                        ' version "' . $version . '"' .
                        ' is not the recommended version "' . $dep->recommended .
                        '", but may be compatible, use --force to install');
                } else {
                    return $this->warning('warning: %s dependency: PHP extension ' .
                        $dep->name . ' version "' . $version . '"' .
                        ' is not the recommended version "' . $dep->recommended . '"');
                }
            }
        }
        return true;
    }

    function validatePhpDependency(PEAR2_Pyrus_PackageFile_v2_Dependencies_Dep $dep)
    {
        if ($this->_state != PEAR2_Pyrus_Validate::INSTALLING &&
              $this->_state != PEAR2_Pyrus_Validate::DOWNLOADING) {
            return true;
        }
        $version = $this->phpversion();
        $extra = $this->_getExtraString($dep);
        if (isset($dep->min)) {
            if (!version_compare($version, $dep->min, '>=')) {
                if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                    return $this->raiseError('%s requires PHP' .
                        $extra . ', installed version is ' . $version);
                } else {
                    return $this->warning('warning: %s requires PHP' .
                        $extra . ', installed version is ' . $version);
                }
            }
        }
        if (isset($dep->max)) {
            if (!version_compare($version, $dep->max, '<=')) {
                if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                    return $this->raiseError('%s requires PHP' .
                        $extra . ', installed version is ' . $version);
                } else {
                    return $this->warning('warning: %s requires PHP' .
                        $extra . ', installed version is ' . $version);
                }
            }
        }
        if (isset($dep->exclude)) {
            foreach ($dep->exclude as $exclude) {
                if (version_compare($version, $exclude, '==')) {
                    if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) &&
                          !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                        return $this->raiseError('%s is not compatible with PHP version ' .
                            $exclude);
                    } else {
                        return $this->warning(
                            'warning: %s is not compatible with PHP version ' .
                            $exclude);
                    }
                }
            }
        }
        return true;
    }

    /**
     * This makes unit-testing a heck of a lot easier
     */
    function getPEARVersion()
    {
        return '@PACKAGE_VERSION@' === '@'.'PACKAGE_VERSION@' ? '2.0.0' : '@PACKAGE_VERSION@';
    }

    function validatePearinstallerDependency(PEAR2_Pyrus_PackageFile_v2_Dependencies_Dep $dep)
    {
        $pearversion = $this->getPEARVersion();
        $extra = $this->_getExtraString($dep);
        if (version_compare($pearversion, $dep->min, '<')) {
            if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                return $this->raiseError('%s requires PEAR Installer' . $extra .
                    ', installed version is ' . $pearversion);
            } else {
                return $this->warning('warning: %s requires PEAR Installer' . $extra .
                    ', installed version is ' . $pearversion);
            }
        }
        if (isset($dep->max)) {
            if (version_compare($pearversion, $dep->max, '>')) {
                if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                    return $this->raiseError('%s requires PEAR Installer' . $extra .
                        ', installed version is ' . $pearversion);
                } else {
                    return $this->warning('warning: %s requires PEAR Installer' . $extra .
                        ', installed version is ' . $pearversion);
                }
            }
        }
        if (isset($dep->exclude)) {
            foreach ($dep->exclude as $exclude) {
                if (version_compare($exclude, $pearversion, '==')) {
                    if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                        return $this->raiseError('%s is not compatible with PEAR Installer ' .
                            'version ' . $exclude);
                    } else {
                        return $this->warning('warning: %s is not compatible with PEAR ' .
                            'Installer version ' . $exclude);
                    }
                }
            }
        }
        return true;
    }

    function validateSubpackageDependency(PEAR2_Pyrus_PackageFile_v2_Dependencies_Package $dep, $params)
    {
        return $this->validatePackageDependency($dep, $params);
    }

    /**
     * @param array dependency information (2.0 format)
     * @param boolean whether this is a required dependency
     * @param array a list of downloaded packages to be installed, if any
     */
    function validatePackageDependency(PEAR2_Pyrus_PackageFile_v2_Dependencies_Package $dep, $params)
    {
        if ($this->_state != PEAR2_Pyrus_Validate::INSTALLING &&
              $this->_state != PEAR2_Pyrus_Validate::DOWNLOADING) {
            return true;
        }
        $required = $dep->deptype == 'required';
        if (isset($dep->providesextension)) {
            if ($this->extension_loaded($dep->providesextension)) {
                $req = $required ? 'required' : 'optional';
                $info = $dep->getInfo();
                $info['name'] = $info['providesextension'];
                $subdep = new PEAR2_Pyrus_PackageFile_v2_Dependencies_Package(
                    $req, 'extension', null, $info, 0);
                $ret = $this->validateExtensionDependency($subdep);
                if ($ret === true) {
                    return true;
                }
            }
        }
        if ($this->_state == PEAR2_Pyrus_Validate::INSTALLING) {
            return $this->_validatePackageInstall($dep);
        }
        if ($this->_state == PEAR2_Pyrus_Validate::DOWNLOADING) {
            return $this->_validatePackageDownload($dep, $params);
        }
    }

    function _validatePackageDownload(PEAR2_Pyrus_PackageFile_v2_Dependencies_Package $dep, $params)
    {
        $required = $dep->deptype === 'required';
        $depname = PEAR2_Pyrus_Config::parsedPackageNameToString(array('package' => $dep->name,
                                                                       'channel' => $dep->channel), true);
        $found = false;
        foreach ($params as $param) {
            if ($param->name == $dep->name && $param->channel == $dep->channel) {
                $found = true;
                break;
            }
        }
        if ($found) {
            $version = $param->version['release'];
            $installed = false;
            $downloaded = true;
        } else {
            if (PEAR2_Pyrus_Config::current()->registry->exists($dep->name, $dep->channel)) {
                $installed = true;
                $downloaded = false;
                $version = PEAR2_Pyrus_Config::current()->registry->info($dep->name,
                    $dep->channel, 'version');
            } else {
                $version = 'not installed or downloaded';
                $installed = false;
                $downloaded = false;
            }
        }
        $extra = $this->_getExtraString($dep);
        if (!isset($dep->min) && !isset($dep->max) &&
              !isset($dep->recommended) && !isset($dep->exclude)) {
            if ($installed || $downloaded) {
                $installed = $installed ? 'installed' : 'downloaded';
                if ($dep->conflicts) {
                    $rest = ", $installed version is " . $version;
                    if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                        return $this->raiseError('%s conflicts with package "' . $depname . '"' .
                            $extra . $rest);
                    } else {
                        return $this->warning('warning: %s conflicts with package "' . $depname . '"' .
                            $extra . $rest);
                    }
                }
                return true;
            } else {
                if ($dep->conflicts) {
                    return true;
                }
                if ($required) {
                    if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                        return $this->raiseError('%s requires package "' . $depname . '"' .
                            $extra);
                    } else {
                        return $this->warning('warning: %s requires package "' . $depname . '"' .
                            $extra);
                    }
                } else {
                    return $this->warning('%s can optionally use package "' . $depname . '"' .
                        $extra);
                }
            }
        }
        if (!$installed && !$downloaded) {
            if ($dep->conflicts) {
                return true;
            }
            if ($required) {
                if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                    return $this->raiseError('%s requires package "' . $depname . '"' .
                        $extra);
                } else {
                    return $this->warning('warning: %s requires package "' . $depname . '"' .
                        $extra);
                }
            } else {
                return $this->warning('%s can optionally use package "' . $depname . '"' .
                    $extra);
            }
        }
        $fail = false;
        if (isset($dep->min)) {
            if (version_compare($version, $dep->min, '<')) {
                $fail = true;
            }
        }
        if (isset($dep->max)) {
            if (version_compare($version, $dep->max, '>')) {
                $fail = true;
            }
        }
        if ($fail && !$dep->conflicts) {
            $installed = $installed ? 'installed' : 'downloaded';
            $dep = PEAR2_Pyrus_Config::parsedPackageNameToString(array('package' => $dep->name,
                                                                       'channel' => $dep->channel), true);
            if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                return $this->raiseError('%s requires package "' . $depname . '"' .
                    $extra . ", $installed version is " . $version);
            } else {
                return $this->warning('warning: %s requires package "' . $depname . '"' .
                    $extra . ", $installed version is " . $version);
            }
        } elseif (!isset($dep->exclude) && (isset($dep->min) || isset($dep->max)) && !$fail &&
              $dep->conflicts) {
            $installed = $installed ? 'installed' : 'downloaded';
            if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                return $this->raiseError('%s conflicts with package "' .
                    $depname . '"' . $extra . ', ' . $installed . ' version is ' . $version);
            } else {
                return $this->warning('warning: %s conflicts with package "' .
                    $depname . '"' . $extra . ', ' . $installed . ' version is ' . $version);
            }
        }
        if (isset($dep->exclude)) {
            // exclude ordinarily tells the installer "install anything but these versions"
            // when paired with conflicts, it becomes "install only these versions"
            $conflicts = $dep->conflicts;
            foreach ($dep->exclude as $exclude) {
                if (version_compare($version, $exclude, '==')) {
                    if ($conflicts) {
                        $fail = false;
                        break;
                    }
                    goto conflict_error;
                } else {
                    if ($conflicts) {
                        $fail = true;
                    }
                }
            }
        } elseif ($dep->conflicts) {
            return true;
        }
        if ($fail) {
conflict_error:
            $installed = $installed ? 'installed' : 'downloaded';
            if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                return $this->raiseError('%s is not compatible with version ' . $version . ' of package "' .
                    $depname . '", ' . $installed . ' version is ' . $version);
            } else {
                return $this->warning('warning: %s is not compatible with version ' . $version . ' of package "' .
                    $depname . '", ' . $installed . ' version is ' . $version);
            }
        }
        if (isset($dep->recommended)) {
            if (version_compare($version, $dep->recommended, '==')) {
                return true;
            } else {
                if (!$found && $installed) {
                    $param = PEAR2_Pyrus_Config::current()->registry->package[$dep->channel . '/' . $dep->name];
                }
                if ($param) {
                    $found = false;
                    foreach ($params as $parent) {
                        if ($parent->name == $this->_currentPackage['package'] &&
                              $parent->channel == $this->_currentPackage['channel']) {
                            $found = true;
                            break;
                        }
                    }
                    if ($found) {
                        if ($param->isCompatible($parent)) {
                            return true;
                        }
                    }
                }
                $installed = $installed ? 'installed' : 'downloaded';
                if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force']) &&
                      !isset(PEAR2_Pyrus_Installer::$options['loose'])) {
                    return $this->raiseError('%s dependency package "' . $depname .
                        '" ' . $installed . ' version ' . $version .
                        ' is not the recommended version ' . $dep->recommended .
                        ', but may be compatible, use --force to install');
                } else {
                    return $this->warning('warning: %s dependency package "' . $depname .
                        '" ' . $installed . ' version ' . $version .
                        ' is not the recommended version ' . $dep->recommended);
                }
            }
        }
        return true;
    }

    function _validatePackageInstall($dep)
    {
        return $this->_validatePackageDownload($dep, array());
    }

    function validatePackageUninstall($dep, $param)
    {
        if ($dep->conflicts) {
            return true; // uninstall OK - these packages conflict (probably installed with --force)
        }
        $required = $dep->deptype == 'required';
        $depname = PEAR2_Pyrus_Config::parsedPackageNameToString(array('package' => $dep->name,
                                                                       'channel' => $dep->channel), true);
        $extra = $this->_getExtraString($dep);
        if (!isset($dep->min) && !isset($dep->max) && !isset($dep->exclude)) {
            if ($required) {
                if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                    return $this->raiseError('"' . $depname . '" is required by ' .
                        'installed package %s' . $extra);
                } else {
                    return $this->warning('warning: "' . $depname . '" is required by ' .
                        'installed package %s' . $extra);
                }
            } else {
                return $this->warning('"' . $depname . '" can be optionally used by ' .
                        'installed package %s' . $extra);
            }
        }
        $version = $param->version['release'];
        $fail = false;
        if (isset($dep->min)) {
            if (version_compare($version, $dep->min, '>=')) {
                $fail = true;
            } else {
                goto nofail;
            }
        }
        if (isset($dep->max)) {
            if (version_compare($version, $dep->max, '<=')) {
                $fail = true;
            }
        }
nofail:
        if (isset($dep->exclude)) {
            $fail = true;
            foreach ($dep->exclude as $exclude) {
                if (version_compare($version, $exclude, '==')) {
                    // rare case - we conflict with the installed package,
                    // so uninstalling is just fine
                    $fail = false;
                    break;
                }
            }
        }
        if ($fail) {
            if ($required) {
                if (!isset(PEAR2_Pyrus_Installer::$options['nodeps']) && !isset(PEAR2_Pyrus_Installer::$options['force'])) {
                    return $this->raiseError($depname . $extra . ' is required by installed package' .
                        ' "%s"');
                } else {
                    return $this->warning('warning: ' . $depname . $extra .
                        ' is required by installed package "%s"');
                }
            } else {
                return $this->warning($depname . $extra . ' can be optionally used by installed package' .
                        ' "%s"');
            }
        }
        return true;
    }

    /**
     * validate a downloaded package against installed packages
     *
     * @param $pkg downloaded package package.xml object
     * @param array $params full list of packages to install
     * @return bool
     */
    function validateDownloadedPackage(PEAR2_Pyrus_IPackageFile $pkg, $params = array())
    {
        $me = $pkg->channel . '/' . $pkg->name;
        $reg = PEAR2_Pyrus_Config::current()->registry;
        $deppackages = $reg->getDependentPackages($pkg);
        $fail = false;
        if ($deppackages) {
            foreach ($deppackages as $packagename) {
                $info = $reg->parsePackageName($packagename);
                foreach ($params as $packd) {
                    if (strtolower($packd->name) == strtolower($info['package']) &&
                          $packd->channel == $info['channel']) {
                        PEAR2_Pyrus_Log::log(3, 'skipping installed package check of "' .
                                    PEAR2_Pyrus_Config::parsedPackageNameToString(
                                        array('channel' => $channel, 'package' => $package),
                                        true) .
                                    '", version "' . $packd->version['release'] . '" will be ' .
                                    'downloaded and installed');
                        continue;
                    }
                    $package = $reg->package[$packagename];
                    $deps = $package->dependencies['required']->package;
                    if (isset($deps[$me])) {
                        $checker = new PEAR2_Pyrus_Dependency_Validator(
                            array('channel' => $pkg->channel, 'package' => $pkg->name),
                            $this->_state, $this->errs);
                        $ret = $checker->_validatePackageDownload($deps[$me], array($pkg, $package));
                    }
                    $deps = $package->dependencies['required']->subpackage;
                    if (isset($deps[$me])) {
                        $checker = new PEAR2_Pyrus_Dependency_Validator(
                            array('channel' => $pkg->channel, 'package' => $pkg->name),
                            $this->_state, $this->errs);
                        $ret = $checker->_validatePackageDownload($deps[$me], array($pkg));
                    }
                }
            }
        }
        if (count($this->errs->E_ERROR)) {
            return $this->raiseError(
                '%s cannot be installed, conflicts with installed packages');
        }
        return true;
    }

    function raiseError($msg)
    {
        if (isset(PEAR2_Pyrus_Installer::$options['ignore-errors'])) {
            return $this->warning($msg);
        }
        $this->errs->E_ERROR[] = new PEAR2_Pyrus_Dependency_Exception(sprintf($msg, PEAR2_Pyrus_Config::parsedPackageNameToString(
            $this->_currentPackage, true)));
        return false;
    }

    function warning($msg)
    {
        $this->errs->E_WARNING[] = new PEAR2_Pyrus_Dependency_Exception(sprintf($msg, PEAR2_Pyrus_Config::parsedPackageNameToString(
            $this->_currentPackage, true)));
        return true;
    }
}
?>
