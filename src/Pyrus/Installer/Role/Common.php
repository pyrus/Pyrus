<?php
/**
 * Base class for all installation roles.
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Base class for all installation roles.
 *
 * This class allows extensibility of file roles.  Packages with complex
 * customization can now provide custom file roles along with the possibility of
 * adding configuration values to match.
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace Pyrus\Installer\Role;
class Common
{
    /**
     * @var \Pyrus\Config
     * @access protected
     */
    protected $config;
    /**
     * @var array
     */
    protected $info;

    /**
     * @param \Pyrus\Config
     */
    function __construct(\Pyrus\Config $config, $info)
    {
        $this->config = $config;
        $this->info = $info;
    }

    /**
     * Retrieve configuration information about a file role from its XML info
     *
     * @param string $role role name
     * @return array
     */
    static function getInfo($role)
    {
        return \Pyrus\Installer\Role::getInfo($role);
    }

    /**
     * Get a location that can be used in an <install as=""/> tag to work with
     * the PEAR Installer.
     * @param \Pyrus\Package $pkg
     * @param array $atts
     * @return string
     */
    function getCompatibleInstallAs(\Pyrus\PackageFileInterface $pkg, $atts)
    {
        $location = $this->getPackagingLocation($pkg, $atts);
        $location = explode('/', $location);
        array_shift($location);
        if (!$this->info['honorsbaseinstall']) {
            array_shift($location);
        }

        $location = implode('/', $location);
        return $location;
    }

    /**
     * Retrieve the location a packaged file should be placed in a package
     *
     * @param \Pyrus\Package $pkg
     * @param array $atts
     * @return string
     */
    function getPackagingLocation(\Pyrus\PackageFileInterface $pkg, $atts)
    {
        if (!$pkg->isNewPackage()) {
            return $atts['name'];
        }
        $role = $this->info['name'];

        $file = $atts['name'];
        // strip role from file path
        // so src/Path/To/File.php becomes Path/To/File.php,
        // data/package.xsd becomes package.xsd
        $newpath = $file;
        if ($role === 'php') {
            if (strpos($newpath, 'src') === 0) {
                $newpath = substr($newpath, 4);
            }
        } elseif (strpos($newpath, $role) === 0) {
            $newpath = substr($newpath, strlen($role) + 1);
        }
        if ($newpath === false) {
            $newpath = $file;
        }

        if ($newpath) {
            if ($newpath[0] == '/') {
                $newpath = substr($newpath, 1);
            }
            $file = $newpath;
        }

        if ($this->info['honorsbaseinstall']) {
            $dest_dir = $role;
            if (array_key_exists('baseinstalldir', $atts)) {
                if ($atts['baseinstalldir'] != '/') {
                    $dest_dir .= '/' . $atts['baseinstalldir'];
                }

                if (strlen($atts['baseinstalldir'])) {
                    $dest_dir .= '/';
                }
            } else {
                $dest_dir .= '/';
            }
        } elseif ($this->info['unusualbaseinstall']) {
            $dest_dir = $role . '/' . $pkg->channel . '/' . $pkg->name  . '/';
            if (array_key_exists('baseinstalldir', $atts)) {
                if (strlen($atts['baseinstalldir']) && $atts['baseinstalldir'] != '/') {
                    $dest_dir .= $atts['baseinstalldir'];
                    if (strlen($atts['baseinstalldir'])) {
                        $dest_dir .= '/';
                    }
                }
            } else {
                if (dirname($file) != '.') {
                    $dest_dir .= dirname($file) . '/';
                }
            }
        } else {
            $dest_dir = $role  . '/' . $pkg->channel. '/' . $pkg->name . '/';
        }

        return $dest_dir . $file;
    }

    function getRelativeLocation(\Pyrus\PackageFileInterface $pkg, \Pyrus\PackageFile\v2Iterator\FileTag $file,
                                 $retDir = false)
    {
        if (!$this->info['locationconfig']) {
            return false;
        }

        if ($this->info['honorsbaseinstall']) {
            $dest_dir = '';
            if ($file->baseinstalldir) {
                $dest_dir .= $file->baseinstalldir;
            }
        } elseif ($this->info['unusualbaseinstall']) {
            if (!$pkg->isNewPackage()) {
                // Place files using the old doc dir structure
                $dest_dir = $pkg->name;
            } else {
                $dest_dir = $pkg->channel . DIRECTORY_SEPARATOR . $pkg->name;
            }
            if ($file->baseinstalldir) {
                $dest_dir .= DIRECTORY_SEPARATOR . $file->baseinstalldir;
            }
        } else {
            $dest_dir = $pkg->channel . DIRECTORY_SEPARATOR . $pkg->name;
        }

        if (dirname($file->name) != '.' && empty($file['install-as'])) {
            $newpath = dirname($file->name);
            if ($pkg->isNewPackage()) {
                // strip role from file path
                // so php/Path/To/File.php becomes Path/To/File.php,
                // data/package.xsd becomes package.xsd
                $r = get_class($this);
                $r = strtolower(substr($r, strrpos($r, '\\') + 1));
                if ($r === 'php') {
                    if (strpos($newpath, 'src') === 0) {
                        $newpath = substr($newpath, 4);
                        if ($newpath === false) {
                            $newpath = '';
                        }
                    }
                }
                if (strpos($newpath, $r) === 0) {
                    $newpath = substr($newpath, strlen($r) + 1);
                    if ($newpath === false) {
                        $newpath = '';
                    }
                }
                $r = $pkg->channel . DIRECTORY_SEPARATOR . $pkg->name;
                if (strpos($newpath, $r) === 0) {
                    // Trim off extra channel and package name directories
                    $newpath = substr($newpath, strlen($r) + 1);
                    if ($newpath === false) {
                        $newpath = '';
                    }
                }
            }
            if ($dest_dir && $newpath) {
                $dest_dir .= DIRECTORY_SEPARATOR;
            }
            $dest_dir .= $newpath;
        }

        if ($dest_dir) {
            $dest_dir .= DIRECTORY_SEPARATOR;
        }
        $dest_file = $dest_dir;
        if (empty($file['install-as'])) {
            $dest_file .= basename($file->name);
        } else {
            $dest_file .= $file['install-as'];
        }
        if ($retDir) {
            // Clean up the DIRECTORY_SEPARATOR mess
            $ds2 = DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR;

            list($dest_dir, $dest_file) = preg_replace(array('!\\\\+!', '!/!', "!$ds2+!"),
                                                        array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR,
                                                              DIRECTORY_SEPARATOR),
                                                        array($dest_dir, $dest_file));
            if ($dest_file[0] == DIRECTORY_SEPARATOR) {
                $dest_file = substr($dest_file, 1);
            }
            return array($dest_dir, $dest_file);
        }
        // Clean up the DIRECTORY_SEPARATOR mess
        $ds2 = DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR;

        $dest_file = preg_replace(array('!\\\\+!', '!/!', "!$ds2+!"),
                                                    array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR,
                                                          DIRECTORY_SEPARATOR),
                                                    $dest_file);
        if ($dest_file[0] == DIRECTORY_SEPARATOR) {
            $dest_file = substr($dest_file, 1);
        }
        return $dest_file;
    }

    /**
     * Get the name of the configuration variable that specifies the location of this file
     * @return string|false
     */
    function getLocationConfig()
    {
        return $this->info['locationconfig'];
    }

    /**
     * Do any unusual setup here
     * @param \Pyrus\Installer
     * @param \Pyrus\PackageFileInterface
     * @param array file attributes
     * @param string file name
     */
    function setup($installer, $pkg, $atts, $file)
    {
    }

    final function packageTimeValidate(\Pyrus\Package $package, array $fileXml)
    {
        if (!isset($this->info['validationmethod'])) {
            return true;
        }
        if (!method_exists($this, $this->info['validationmethod'])) {
            \Pyrus\Logger::log(0, 'WARNING: custom role ' . $this->info['name'] .
                                 ' specifies non-existing validation method ' .
                                 $this->info['validationmethod']);
            return true;
        }
        return $this->{$this->info['validationmethod']}($package, $fileXml);
    }

    function isExecutable()
    {
        return $this->info['executable'];
    }

    function isInstallable()
    {
        return $this->info['installable'];
    }
}