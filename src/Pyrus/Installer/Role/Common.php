<?php
/**
 * Base class for all installation roles.
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   pear
 * @package    PEAR
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  1997-2006 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: Common.php,v 1.2 2007/06/03 06:06:38 cellog Exp $
 * @link       http://pear.php.net/package/PEAR
 * @since      File available since Release 1.4.0a1
 */
/**
 * Base class for all installation roles.
 *
 * This class allows extensibility of file roles.  Packages with complex
 * customization can now provide custom file roles along with the possibility of
 * adding configuration values to match.
 * @category   pear
 * @package    PEAR
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  1997-2006 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PEAR
 * @since      Class available since Release 1.4.0a1
 */
class PEAR2_Pyrus_Installer_Role_Common
{
    /**
     * @var PEAR_Config
     * @access protected
     */
    protected $config;

    /**
     * @param PEAR2_Pyrus_Config
     */
    function __construct(PEAR2_Pyrus_Config $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieve configuration information about a file role from its XML info
     *
     * @param string $role Role Classname, as in "PEAR2_Pyrus_Installer_Role_Data"
     * @return array
     */
    static function getInfo($role)
    {
        return PEAR2_Pyrus_Installer_Role::getInfo($role);
    }

    /**
     * Retrieve the location a packaged file should be placed in a package
     *
     * @param PEAR2_Pyrus_Package $pkg
     * @param array $atts
     * @return string
     */
    function getPackagingLocation(PEAR2_Pyrus_PackageFile_v2 $pkg, $atts)
    {
        $roleInfo = PEAR2_Pyrus_Installer_Role::getInfo('PEAR2_Pyrus_Installer_Role_' . 
            ucfirst(str_replace('pear2_pyrus_installer_role_', '', strtolower(get_class($this)))));
        $role = str_replace('pear2_pyrus_installer_role_', '',
                strtolower(get_class($this)));
        $file = $atts['name'];
        // strip role from file path
        // so src/Path/To/File.php becomes Path/To/File.php,
        // data/package.xsd becomes package.xsd
        $newpath = $file;
        if (strpos($newpath, $role) === 0) {
            $newpath = substr($newpath, strlen($role) + 1);
            if ($newpath === false) {
                $newpath = $file;
            }
        } elseif ($role === 'php' && strpos($newpath, 'src') === 0) {
            $newpath = substr($newpath, 4);
            if ($newpath === false) {
                $newpath = $file;
            }
        }
        if ($newpath) {
            $file = $newpath;
        }
        if ($roleInfo['honorsbaseinstall']) {
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
        } elseif ($roleInfo['unusualbaseinstall']) {
            $dest_dir = $role . '/' . $pkg->channel . '/' . $pkg->name . '/';
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
            $dest_dir = $role . '/' . $pkg->channel . '/' . $pkg->name . '/';
        }
        return $dest_dir . $file;
    }

    /**
     * This is called for each file to set up the directories and files
     * @param PEAR2_Pyrus_Package
     * @param array attributes from the <file> tag
     * @param string file name
     * @return array an array consisting of:
     *
     *    1 the original, pre-baseinstalldir installation directory
     *    2 the final installation directory
     *    3 the full path to the final location of the file
     *    4 the location of the pre-installation file
     */
    function processInstallation(PEAR2_Pyrus_Package $pkg, $atts, $file, $tmp_path, $layer = null)
    {
        $roleInfo = PEAR2_Pyrus_Installer_Role::getInfo('PEAR2_Pyrus_Installer_Role_' . 
            ucfirst(str_replace('pear2_pyrus_installer_role_', '', strtolower(get_class($this)))));
        if (!$roleInfo['locationconfig']) {
            return false;
        }
        $where = $this->config->{$roleInfo['locationconfig']};
        if ($roleInfo['honorsbaseinstall']) {
            $dest_dir = $save_destdir =
                $where;
            if (!empty($atts['baseinstalldir'])) {
                $dest_dir .= DIRECTORY_SEPARATOR . $atts['baseinstalldir'];
            }
        } elseif ($roleInfo['unusualbaseinstall']) {
            $dest_dir = $save_destdir = $where .
                DIRECTORY_SEPARATOR . $pkg->channel . DIRECTORY_SEPARATOR . $pkg->name;
            if (!empty($atts['baseinstalldir'])) {
                $dest_dir .= DIRECTORY_SEPARATOR . $atts['baseinstalldir'];
            }
        } else {
            $dest_dir = $save_destdir = $where .
                DIRECTORY_SEPARATOR . $pkg->channel . DIRECTORY_SEPARATOR . $pkg->name;
        }
        if (dirname($file) != '.' && empty($atts['install-as'])) {
            if ($pkg->isNewPackage()) {
                // strip role from file path
                // so php/Path/To/File.php becomes Path/To/File.php,
                // data/package.xsd becomes package.xsd
                $newpath = dirname($file);
                if (strpos($newpath, $r = strtolower(str_replace('PEAR2_Pyrus_Installer_Role_', '',
                      get_class($this)))) === 0) {
                    $newpath = substr($newpath, strlen($r) + 1);
                    if ($newpath === false) {
                        $newpath = '';
                    }
                } elseif ($r === 'php' && strpos($newpath, $r = 'src') === 0) {
                    $newpath = substr($newpath, strlen($r) + 1);
                    if ($newpath === false) {
                        $newpath = '';
                    }
                }
            }
            $dest_dir .= DIRECTORY_SEPARATOR . $newpath;
        }
        if (empty($atts['install-as'])) {
            $dest_file = $dest_dir . DIRECTORY_SEPARATOR . basename($file);
        } else {
            $dest_file = $dest_dir . DIRECTORY_SEPARATOR . $atts['install-as'];
        }
        $orig_file = $tmp_path . DIRECTORY_SEPARATOR . $file;

        // Clean up the DIRECTORY_SEPARATOR mess
        $ds2 = DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR;
        
        list($dest_dir, $dest_file, $orig_file) = preg_replace(array('!\\\\+!', '!/!', "!$ds2+!"),
                                                    array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR,
                                                          DIRECTORY_SEPARATOR),
                                                    array($dest_dir, $dest_file, $orig_file));
        return array($save_destdir, $dest_dir, $dest_file, $orig_file);
    }

    /**
     * Get the name of the configuration variable that specifies the location of this file
     * @return string|false
     */
    function getLocationConfig()
    {
        $roleInfo = PEAR2_Pyrus_Installer_Role::getInfo('PEAR2_Pyrus_Installer_Role_' . 
            ucfirst(str_replace('pear2_pyrus_installer_role_', '', strtolower(get_class($this)))));
        return $roleInfo['locationconfig'];
    }

    /**
     * Do any unusual setup here
     * @param PEAR_Installer
     * @param PEAR_PackageFile_v2
     * @param array file attributes
     * @param string file name
     */
    function setup($installer, $pkg, $atts, $file)
    {
    }

    function isExecutable()
    {
        $roleInfo = PEAR2_Pyrus_Installer_Role::getInfo('PEAR2_Pyrus_Installer_Role_' . 
            ucfirst(str_replace('pear2_pyrus_installer_role_', '', strtolower(get_class($this)))));
        return $roleInfo['executable'];
    }

    function isInstallable()
    {
        $roleInfo = PEAR2_Pyrus_Installer_Role_Common::getInfo('PEAR2_Pyrus_Installer_Role_' . 
            ucfirst(str_replace('pear2_pyrus_installer_role_', '', strtolower(get_class($this)))));
        return $roleInfo['installable'];
    }

    function isExtension()
    {
        $roleInfo = PEAR2_Pyrus_Installer_Role::getInfo('PEAR2_Pyrus_Installer_Role_' . 
            ucfirst(str_replace('pear2_pyrus_installer_role_', '', strtolower(get_class($this)))));
        return $roleInfo['phpextension'];
    }
}
?>