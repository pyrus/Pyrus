<?php
/**
 * \PEAR2\Pyrus\Dependency\Validator, advanced dependency validation
 *
 * PHP versions 4 and 5
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
 * Dependency check for PEAR2 packages
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus\Dependency;
use \PEAR2\Pyrus\Main as Main, \PEAR2\Pyrus\Config as Config, \PEAR2\Pyrus\Validate as Validate;
class Validator
{
    /**
     * @var \PEAR2\MultiErrors
     */
    protected $errs;

    /**
     * One of the \PEAR2\Pyrus\Validate::* states
     * @see \PEAR2\Pyrus\Validate::NORMAL
     * @var integer
     */
    var $_state;

    /**
     * @var \PEAR2\Pyrus\OSGuess
     */
    var $_os;

    /**
     * Package to validate
     * @var \PEAR2\Pyrus\Package
     */
    var $_currentPackage;

    /**
     * @param \PEAR2\Pyrus\Package
     * @param int installation state (one of \PEAR2\Pyrus\Validate::*)
     * @param \PEAR2\MultiErrors
     */
    function __construct($package, $state = Validate::INSTALLING, \PEAR2\MultiErrors $errs)
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
        $this->_os = new \PEAR2\Pyrus\OSGuess;
        return $this->_os->getSysname();
    }

    /**
     * Specify a dependency on an OS.  Use arch for detailed os/processor information
     *
     * There are two generic OS dependencies that will be the most common, unix and windows.
     * Other options are linux, freebsd, darwin (OS X), sunos, irix, hpux, aix
     */
    function validateOsDependency(\PEAR2\Pyrus\PackageFile\v2\Dependencies\Dep $dep)
    {
        if ($this->_state != Validate::INSTALLING && $this->_state != Validate::DOWNLOADING) {
            return true;
        }

        if ($dep->name == '*') {
            return true; // no one will do conflicts with *, so assume no conflicts
        }

        switch (strtolower($dep->name)) {
            case 'windows' :
                if ($dep->conflicts) {
                    if (strtolower(substr($this->getPHP_OS(), 0, 3)) == 'win') {
                        $msg = "Cannot install %s on Windows";
                        if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                            return $this->raiseError($msg);
                        }

                        return $this->warning("warning: " . $msg);
                    }
                } else {
                    if (strtolower(substr($this->getPHP_OS(), 0, 3)) != 'win') {
                        $msg = "Can only install %s on Windows";
                        if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                            return $this->raiseError($msg);
                        }

                        return $this->warning("warning: " . $msg);
                    }
                }
            break;
            case 'unix' :
                $unices = array('linux', 'freebsd', 'darwin', 'sunos', 'irix', 'hpux', 'aix');
                if ($dep->conflicts) {
                    if (in_array(strtolower($this->getSysname()), $unices)) {
                        $msg = "Cannot install %s on any Unix system";
                        if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                            return $this->raiseError($msg);
                        }

                        return $this->warning("warning: " . $msg);
                    }
                } else {
                    if (!in_array(strtolower($this->getSysname()), $unices)) {
                        $msg = "Can only install %s on a Unix system";
                        if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                            return $this->raiseError($msg);
                        }

                        return $this->warning("warning: " . $msg);
                    }
                }
            break;
            default :
                if ($dep->conflicts) {
                    if (strtolower($dep->name) == strtolower($this->getSysname())) {
                        $msg = 'Cannot install %s on ' . $dep->name . ' operating system';
                        if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                            return $this->raiseError($msg);
                        }

                        return $this->warning('warning: ' . $msg);
                    }
                } else {
                    if (strtolower($dep->name) != strtolower($this->getSysname())) {
                        $msg = 'Cannot install %s on ' . $this->getSysname() .
                                ' operating system, can only install on ' . $dep->name;
                        if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                            return $this->raiseError($msg);
                        }

                        return $this->warning('warning: ' . $msg);
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
        $this->_os = new \PEAR2\Pyrus\OSGuess;
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
        if ($this->_state != Validate::INSTALLING) {
            return true;
        }

        if ($this->matchSignature($dep->pattern)) {
            if ($dep->conflicts) {
                $msg = '%s Architecture dependency failed, cannot match "' . $dep->pattern . '"';
                if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                    return $this->raiseError($msg);
                }

                return $this->warning('warning: ' . $msg);
            }

            return true;
        }

        if ($dep->conflicts) {
            return true;
        }

        $msg = '%s Architecture dependency failed, does not match "' . $dep->pattern . '"';
        if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
            return $this->raiseError($msg);
        }

        return $this->warning('warning: ' . $msg);
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
        }

        return phpversion();
    }

    function validateExtensionDependency(\PEAR2\Pyrus\PackageFile\v2\Dependencies\Package $dep)
    {
        if ($this->_state != Validate::INSTALLING && $this->_state != Validate::DOWNLOADING) {
            return true;
        }

        $required = $dep->deptype == 'required';
        $loaded   = $this->extension_loaded($dep->name);
        $extra    = $this->_getExtraString($dep);
        if (!isset($dep->min) && !isset($dep->max) &&
              !isset($dep->recommended) && !isset($dep->exclude)
        ) {
            if ($loaded) {
                if ($dep->conflicts) {
                    $msg = '%s conflicts with PHP extension "' . $dep->name . '"' . $extra;
                    if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                        return $this->raiseError($msg);
                    }

                    return $this->warning('warning: ' . $msg);
                }

                return true;
            }

            if ($dep->conflicts) {
                return true;
            }

            if ($required) {
                $msg = '%s requires PHP extension "' . $dep->name . '"' . $extra;
                if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                    return $this->raiseError($msg);
                }

                return $this->warning('warning: ' . $msg);

            }

            return $this->warning('%s can optionally use PHP extension "' .
                    $dep->name . '"' . $extra);
        }

        if (!$loaded) {
            if ($dep->conflicts) {
                return true;
            }

            if (!$required) {
                return $this->warning('%s can optionally use PHP extension "' .
                    $dep->name . '"' . $extra);
            }

            $msg = '%s requires PHP extension "' . $dep->name . '"' . $extra;
            if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                return $this->raiseError($msg);
            }

            return $this->warning('warning: ' . $msg);
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
            $msg = '%s requires PHP extension "' . $dep->name . '"' . $extra .
                    ', installed version is ' . $version;
            if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                return $this->raiseError($msg);
            }

            return $this->warning('warning: ' . $msg);
        } elseif (!isset($dep->exclude) && (isset($dep->min) || isset($dep->max)) && !$fail && $dep->conflicts) {
            $msg = '%s conflicts with PHP extension "' . $dep->name . '"' . $extra .
                    ', installed version is ' . $version;
            if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                return $this->raiseError($msg);
            }

            return $this->warning('warning: ' . $msg);
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
                    $fail = true;
                    break;
                } else {
                    if ($conflicts) {
                        $fail = true;
                    }
                }
            }
        }

        if ($fail) {
conflict_error:
            $msg = '%s is not compatible with version ' . $version . ' of PHP extension "' .
                    $dep->name . '", installed version is ' . $version;
            if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                return $this->raiseError($msg);
            }

            return $this->warning('warning: ' . $msg);
        }

        if (isset($dep->recommended)) {
            if (version_compare($version, $dep->recommended, '==')) {
                return true;
            }

            $msg = '%s dependency: PHP extension ' . $dep->name .
                    ' version "' . $version . '"' .
                    ' is not the recommended version "' . $dep->recommended . '"';
            if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                return $this->raiseError($msg . ', but may be compatible, use --force to install');
            }

            return $this->warning('warning: ' . $msg);
        }

        return true;
    }

    function validatePhpDependency(\PEAR2\Pyrus\PackageFile\v2\Dependencies\Dep $dep)
    {
        if ($this->_state != Validate::INSTALLING && $this->_state != Validate::DOWNLOADING) {
            return true;
        }

        $version = $this->phpversion();
        $extra   = $this->_getExtraString($dep);
        if (isset($dep->min)) {
            if (!version_compare($version, $dep->min, '>=')) {
                $msg = '%s requires PHP' . $extra . ', installed version is ' . $version;
                if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                    return $this->raiseError($msg);
                }

                return $this->warning('warning: ' . $msg);
            }
        }

        if (isset($dep->max)) {
            if (!version_compare($version, $dep->max, '<=')) {
                $msg ='%s requires PHP' . $extra . ', installed version is ' . $version;
                if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                    return $this->raiseError($msg);
                }

                return $this->warning('warning: ' . $msg);
            }
        }

        if (isset($dep->exclude)) {
            foreach ($dep->exclude as $exclude) {
                if (version_compare($version, $exclude, '==')) {
                    $msg = '%s is not compatible with PHP version ' . $exclude;
                    if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                        return $this->raiseError($msg);
                    }

                    return $this->warning('warning: ' . $msg);
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

    function validatePearinstallerDependency(\PEAR2\Pyrus\PackageFile\v2\Dependencies\Dep $dep)
    {
        $pearversion = $this->getPEARVersion();
        $extra = $this->_getExtraString($dep);
        if (version_compare($pearversion, $dep->min, '<')) {
            $msg = '%s requires PEAR Installer' . $extra . ', installed version is ' . $pearversion;
            if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                return $this->raiseError($msg);
            }

            return $this->warning('warning: ' . $msg);
        }

        if (isset($dep->max)) {
            if (version_compare($pearversion, $dep->max, '>')) {
                $msg = '%s requires PEAR Installer' . $extra . ', installed version is ' . $pearversion;
                if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                    return $this->raiseError($msg);
                }

                return $this->warning('warning: ' . $msg);
            }
        }

        if (isset($dep->exclude)) {
            foreach ($dep->exclude as $exclude) {
                if (version_compare($exclude, $pearversion, '==')) {
                    $msg = '%s is not compatible with PEAR Installer version ' . $exclude;
                    if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                        return $this->raiseError($msg);
                    }

                    return $this->warning('warning: ' . $msg);
                }
            }
        }

        return true;
    }

    function validateSubpackageDependency(\PEAR2\Pyrus\PackageFile\v2\Dependencies\Package $dep, $params)
    {
        return $this->validatePackageDependency($dep, $params);
    }

    /**
     * @param array dependency information (2.0 format)
     * @param boolean whether this is a required dependency
     * @param array a list of downloaded packages to be installed, if any
     */
    function validatePackageDependency(\PEAR2\Pyrus\PackageFile\v2\Dependencies\Package $dep, $params)
    {
        if ($this->_state != Validate::INSTALLING && $this->_state != Validate::DOWNLOADING) {
            return true;
        }

        $required = $dep->deptype == 'required';
        if (isset($dep->providesextension)) {
            if ($this->extension_loaded($dep->providesextension)) {
                $req = $required ? 'required' : 'optional';
                $info = $dep->getInfo();
                $info['name'] = $info['providesextension'];
                $subdep = new \PEAR2\Pyrus\PackageFile\v2\Dependencies\Package(
                    $req, 'extension', null, $info, 0);
                $ret = $this->validateExtensionDependency($subdep);
                if ($ret === true) {
                    return true;
                }
            }
        }

        if ($this->_state == Validate::INSTALLING) {
            return $this->_validatePackageInstall($dep);
        }

        if ($this->_state == Validate::DOWNLOADING) {
            return $this->_validatePackageDownload($dep, $params);
        }
    }

    function _validatePackageDownload(\PEAR2\Pyrus\PackageFile\v2\Dependencies\Package $dep, $params)
    {
        $required = $dep->deptype === 'required';
        $depname = Config::parsedPackageNameToString(array('package' => $dep->name,
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
            if (Config::current()->registry->exists($dep->name, $dep->channel)) {
                $installed = true;
                $downloaded = false;
                $version = Config::current()->registry->info($dep->name,
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
                    $msg = '%s conflicts with package "' . $depname . '"' . $extra . $rest;
                    if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                        return $this->raiseError($msg);
                    }

                    return $this->warning('warning: ' . $msg);
                }

                return true;
            }

            if ($dep->conflicts) {
                return true;
            }

            if ($required) {
                $msg = '%s requires package "' . $depname . '"' . $extra;
                if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                    return $this->raiseError($msg);
                }

                return $this->warning('warning: ' . $msg);
            }

            return $this->warning('%s can optionally use package "' . $depname . '"' . $extra);
        }

        if (!$installed && !$downloaded) {
            if ($dep->conflicts) {
                return true;
            }

            if ($required) {
                $msg = '%s requires package "' . $depname . '"' . $extra;
                if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                    return $this->raiseError($msg);
                }

                return $this->warning('warning: ' . $msg);
            }

            return $this->warning('%s can optionally use package "' . $depname . '"' . $extra);
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
            $dep = Config::parsedPackageNameToString(array('package' => $dep->name,
                                                           'channel' => $dep->channel), true);

            $msg = '%s requires package "' . $depname . '"' . $extra . ", $installed version is " . $version;
            if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                return $this->raiseError($msg);
            }

            return $this->warning('warning: ' . $msg);
        } elseif (!isset($dep->exclude) && (isset($dep->min) || isset($dep->max)) && !$fail && $dep->conflicts) {
            $installed = $installed ? 'installed' : 'downloaded';

            $msg = '%s conflicts with package "' . $depname . '"' . $extra . ', ' .
                    $installed . ' version is ' . $version;
            if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                return $this->raiseError($msg);
            }

            return $this->warning('warning: ' . $msg);
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
                    $fail = true;
                    break;
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
            $installed = $installed ? 'installed' : 'downloaded';
            $msg = '%s is not compatible with version ' . $version . ' of package "' .
                    $depname . '", ' . $installed . ' version is ' . $version;
            if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                return $this->raiseError($msg);
            }

            return $this->warning('warning: ' . $msg);
        }

        if (isset($dep->recommended)) {
            if (version_compare($version, $dep->recommended, '==')) {
                return true;
            }

            if (!$found && $installed) {
                $param = Config::current()->registry->package[$dep->channel . '/' . $dep->name];
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
            $msg = '%s dependency package "' . $depname . '" ' . $installed . ' version ' .
                    $version . ' is not the recommended version ' . $dep->recommended;
            if (
                !isset(Main::$options['nodeps']) &&
                !isset(Main::$options['force']) &&
                !isset(Main::$options['loose'])
            ) {
                return $this->raiseError($msg . ', but may be compatible, use --force to install');
            }

            return $this->warning('warning: ' . $msg);
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
        $depname = Config::parsedPackageNameToString(array('package' => $dep->name,
                                                                       'channel' => $dep->channel), true);
        $extra = $this->_getExtraString($dep);
        if (!isset($dep->min) && !isset($dep->max) && !isset($dep->exclude)) {
            if ($required) {
                $msg = '"' . $depname . '" is required by installed package %s' . $extra;
                if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                    return $this->raiseError($msg);
                }

                return $this->warning('warning: ' . $msg);
            }

            return $this->warning('"' . $depname . '" can be optionally used by ' .
                    'installed package %s' . $extra);
        }

        $version = $param->version['release'];
        $fail = false;
		if (isset($dep->max) && version_compare($version, $dep->max, '<=')) {
			$fail = true;
		}
        if (isset($dep->min)) {
            if (version_compare($version, $dep->min, '>=')) {
                $fail = true;
            } else {
				$fail = false;
			}
        }
        
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
                $msg = $depname . $extra . ' is required by installed package "%s"';
                if (!isset(Main::$options['nodeps']) && !isset(Main::$options['force'])) {
                    return $this->raiseError($msg);
                }

                return $this->warning('warning: ' . $msg);
            }

            return $this->warning($depname . $extra . ' can be optionally used by installed package' .
                    ' "%s"');
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
    function validateDownloadedPackage(\PEAR2\Pyrus\PackageFileInterface $pkg, $params = array())
    {
        $me = $pkg->channel . '/' . $pkg->name;
        $reg = Config::current()->registry;
        $deppackages = $reg->getDependentPackages($pkg);
        $fail = false;
        if ($deppackages) {
            $actual = array();
            // first, remove packages that will be installed
            foreach ($deppackages as $package) {
                foreach ($params as $packd) {
                    if (strtolower($packd->name) == strtolower($package->name) &&
                          $packd->channel == $package->channel) {
                        \PEAR2\Pyrus\Logger::log(3, 'skipping installed package check of "' .
                                    Config::parsedPackageNameToString(
                                        array('channel' => $package->channel, 'package' => $package->name),
                                        true) .
                                    '", version "' . $packd->version['release'] . '" will be ' .
                                    'downloaded and installed');
                        continue 2;
                    }
                }

                $actual[] = $package;
            }

            foreach ($actual as $package) {
                $checker = new \PEAR2\Pyrus\Dependency\Validator(
                    array('channel' => $package->channel, 'package' => $package->name),
                    $this->_state, $this->errs);
                foreach ($params as $packd) {
                    $deps = $package->dependencies['required']->package;
                    if (isset($deps[$me])) {
                        $ret = $checker->_validatePackageDownload($deps[$me], array($pkg, $package));
                    }

                    $deps = $package->dependencies['required']->subpackage;
                    if (isset($deps[$me])) {
                        $ret = $checker->_validatePackageDownload($deps[$me], array($pkg));
                    }

                    $deps = $package->dependencies['optional']->package;
                    if (isset($deps[$me])) {
                        $ret = $checker->_validatePackageDownload($deps[$me], array($pkg, $package));
                    }

                    $deps = $package->dependencies['optional']->subpackage;
                    if (isset($deps[$me])) {
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
        if (isset(Main::$options['ignore-errors'])) {
            return $this->warning($msg);
        }

        $package = Config::parsedPackageNameToString($this->_currentPackage, true);
        $this->errs->E_ERROR[] = new Exception(sprintf($msg, $package));
        return false;
    }

    function warning($msg)
    {
        $package = Config::parsedPackageNameToString($this->_currentPackage, true);
        $this->errs->E_WARNING[] = new Exception(sprintf($msg, $package));
        return true;
    }
}