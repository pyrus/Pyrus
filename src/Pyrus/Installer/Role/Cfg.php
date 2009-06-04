<?php
/**
 * PEAR2_Pyrus_Installer_Role_Cfg
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
 * user-customizable configuration role
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Installer_Role_Cfg extends PEAR2_Pyrus_Installer_Role_Common
{
    protected $md5 = null;
    /**
     * Do any unusual setup here
     * @param PEAR_Installer
     * @param PEAR_PackageFile_v2
     * @param array file attributes
     * @param string file name
     */
    function setup($installer, $pkg, $atts, $file)
    {
        $reg = PEAR2_Pyrus_Config::current()->registry;
        if (!$reg->exists($pkg->name, $pkg->channel)) {
            return;
        }
        $package = $reg->package[$pkg->channel . '/' . $pkg->name];
        if (isset($package->files[$file]) && isset($package->files[$file]['attribs']['md5sum'])) {
            $this->md5 = $package->files[$file]['attribs']['md5sum'];
        }
    }

    function getRelativeLocation(PEAR2_Pyrus_IPackageFile $pkg, PEAR2_Pyrus_PackageFile_v2Iterator_FileTag $file,
                                 $retDir = false)
    {
        if ($this->md5 === null) {
            return parent::getRelativeLocation($pkg, $file, $retDir);
        }
        $info = parent::getRelativeLocation($pkg, $file, $retDir);
        if ($retDir) {
            $filepath = PEAR2_Pyrus_Config::current()->cfg_dir .
                    DIRECTORY_SEPARATOR . $info[1];
        } else {
            $filepath = PEAR2_Pyrus_Config::current()->cfg_dir .
                    DIRECTORY_SEPARATOR . $info;
        }
        if (@file_exists($filepath)) {
            // configuration has already been installed, check for modifications
            // made by the user
            $md5 = md5_file($filepath);
            $newmd5 = $pkg->files[$file->packagedname]['attribs'];
            if (!isset($newmd5['md5sum'])) {
                $newmd5 = md5_file($pkg->getFilePath($file->packagedname));
            } else {
                $newmd5 = $newmd5['md5sum'];
            }
            // first check to see if the user modified the file
            // next check to see if the config file changed from the last installed version
            // if both tests are satisfied, install the new file under another name and display a warning
            if ($md5 !== $this->md5 && $md5 !== $newmd5) {
                // configuration has been modified, so save our version as
                // configfile.new-version
                $old = $filepath;
                $filepath .= '.new-' . $pkg->getVersion();
                PEAR2_Pyrus::log(0, "WARNING: configuration file $old is being installed as $filepath, " .
                                    "you should manually merge in changes to the existing configuration file");
            }
        }
        if ($retDir) {
            $info[1] = $filepath;
        }
        return $info;
    }
}
?>