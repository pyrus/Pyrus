<?php
/**
 * \pear2\Pyrus\Package
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
 * Abstract representation of a package
 *
 * specific package types are:
 *
 * - package.xml
 * - package.tgz/package.tar
 * - package.phar
 * - remote undownloaded package
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus;
class Package implements \pear2\Pyrus\IPackage
{
    /**
     * The actual package representation
     *
     * @var \pear2\Pyrus\Package\Xml|\pear2\Pyrus\Package\Tar|\pear2\Pyrus\Package\Phar
     */
    protected $internal;
    protected $from;
    /**
     * Used when packaging up a package that should be ultra-backwards compatible
     */
    protected $saveAsPackage2_xml = false;

    function __construct($packagedescription, $forceremote = false)
    {
        if (!$packagedescription) {
            return;
        }
        $class = $this->parsePackageDescription($packagedescription, $forceremote);
        $this->internal = new $class($packagedescription, $this);
    }

    function isOldAndCrustyCompatible()
    {
        return $this->saveAsPackage2_xml;
    }

    /**
     * Tell the package creator to save package.xml as package2.xml
     * and to grab package.xml from cwd.
     *
     * This is used to package up things like the PEAR Installer that need
     * to still be compatible with PEAR 1.3.x
     */
    function thisIsOldAndCrustyCompatible()
    {
        $this->saveAsPackage2_xml = true;
    }

    function setFrom($from)
    {
        $this->from = $from;
    }

    function __get($var)
    {
        return $this->internal->$var;
    }
 
    function __set($var, $value)
    {
        return $this->internal->__set($var, $value);
    }

    function __call($func, $args)
    {
        // delegate to the internal object
        return call_user_func_array(array($this->internal, $func), $args);
    }

    function getValidator()
    {
        return $this->internal->getValidator();
    }

    function toArray($forpackaging = false)
    {
        return $this->internal->toArray($forpackaging);
    }

    function getFileContents($file, $asstream = false)
    {
        return $this->internal->getFileContents($file, $asstream);
    }

    function getFilePath($file)
    {
        return $this->internal->getFilePath($file);
    }

    function isNewPackage()
    {
        return $this->internal->isNewPackage();
    }

    function isUpgradeable()
    {
        return $this->internal->isUpgradeable();
    }

    function getFrom()
    {
        if ($this->from) {
            return $this->from->getFrom();
        }
        return $this;
    }

    function getPackageFileObject()
    {
        return $this->internal->getPackageFileObject();
    }

    function getInternalPackage()
    {
        return $this->internal;
    }

    function setInternalPackage(\pear2\Pyrus\IPackage $internal)
    {
        $this->internal = $internal;
    }

    function __toString()
    {
        return $this->internal->__toString();
    }

    function offsetExists($offset)
    {
        return isset($this->internal[$offset]);
    }

    function offsetGet($offset)
    {
        return $this->internal[$offset];
    }

    function offsetSet($offset, $value)
    {
        $this->internal[$offset] = $value;
    }

    function offsetUnset($offset)
    {
        unset($this->internal[$offset]);
    }

    function isStatic()
    {
        return $this->internal->isStatic();
    }

    function isRemote()
    {
        return $this->internal instanceof \pear2\Pyrus\Package\Remote ||
                    $this->internal instanceof \pear2\Pyrus\Channel\Remotepackage || (
                    $this->internal instanceof \pear2\Pyrus\Package && $this->internal->isRemote());
    }

    function download()
    {
        if ($this->internal instanceof \pear2\Pyrus\Package\Remote) {
            $this->internal = $this->internal->download();
        }
    }

    function copyTo($where)
    {
        $this->internal->copyTo($where);
    }

    static function parsePackageDescription($package, $forceremote = false)
    {
        if (strpos($package, 'http://') === 0 || strpos($package, 'https://') === 0) {
            return 'pear2\Pyrus\Package\Remote';
        }
        try {
            if (!$forceremote && @file_exists($package) && @is_file($package)) {
                $info = pathinfo($package);
                if (!isset($info['extension']) || !strlen($info['extension'])) {
                    // guess based on first 4 characters
                    $f = @fopen($package, 'r');
                    if ($f) {
                        $first5 = fread($f, 5);
                        fclose($f);
                        if ($first5 == '<?xml') {
                            return 'pear2\Pyrus\Package\Xml';
                        }
                        return 'pear2\Pyrus\Package\Phar';
                    }
                } else {
                    if (extension_loaded('phar') && strtolower($info['extension']) != 'xml') {
                        return 'pear2\Pyrus\Package\Phar';
                    }
                    switch (strtolower($info['extension'])) {
                        case 'xml' :
                            return 'pear2\Pyrus\Package\Xml';
                        default:
                            throw new \pear2\Pyrus\Package\Exception('Cannot read archives with phar extension');
                    }
                }
            }
            $info = \pear2\Pyrus\Config::parsePackageName($package);
            return 'pear2\Pyrus\Package\Remote';
        } catch (\Exception $e) {
            throw new \pear2\Pyrus\Package\Exception('package "' . $package . '" is unknown', $e);
        }
    }
}
