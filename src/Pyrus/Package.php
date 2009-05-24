<?php
/**
 * PEAR2_Pyrus_Package
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
class PEAR2_Pyrus_Package implements PEAR2_Pyrus_IPackage
{
    /**
     * This is strictly for unit-testing purposes
     * @var string
     */
    public $archivefile;
    /**
     * The actual package representation
     *
     * @var PEAR2_Pyrus_Package_Xml|PEAR2_Pyrus_Package_Tar|PEAR2_Pyrus_Package_Phar
     */
    protected $internal;
    protected $from;

    function __construct($packagedescription, $forceremote = false)
    {
        if (!$packagedescription) {
            return;
        }
        if ($forceremote) {
            $this->internal = new PEAR2_Pyrus_Package_Remote($packagedescription);
        } else {
            $class = $this->parsePackageDescription($packagedescription);
            $this->internal = new $class($packagedescription, $this);
        }
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

    function setInternalPackage(PEAR2_Pyrus_IPackage $internal)
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
        return $this->internal instanceof PEAR2_Pyrus_Package_Remote ||
                    $this->internal instanceof PEAR2_Pyrus_Channel_Remotepackage || (
                    $this->internal instanceof PEAR2_Pyrus_Package && $this->internal->isRemote());
    }

    function download()
    {
        if ($this->internal instanceof PEAR2_Pyrus_Package_Remote) {
            $this->internal = $this->internal->download();
        }
    }

    static function parsePackageDescription($package)
    {
        if (strpos($package, 'http://') === 0 || strpos($package, 'https://') === 0) {
            return 'PEAR2_Pyrus_Package_Remote';
        }
        try {
            if (@file_exists($package) && @is_file($package)) {
                $info = pathinfo($package);
                if (!isset($info['extension']) || !strlen($info['extension'])) {
                    // guess based on first 4 characters
                    $f = @fopen($package, 'r');
                    if ($f) {
                        $first5 = fread($f, 5);
                        fclose($f);
                        if ($first5 == '<?xml') {
                            return 'PEAR2_Pyrus_Package_Xml';
                        }
                        return 'PEAR2_Pyrus_Package_Phar';
                    }
                } else {
                    if (extension_loaded('phar') && strtolower($info['extension']) != 'xml') {
                        return 'PEAR2_Pyrus_Package_Phar';
                    }
                    switch (strtolower($info['extension'])) {
                        case 'xml' :
                            return 'PEAR2_Pyrus_Package_Xml';
                        default:
                            throw new PEAR2_Pyrus_Package_Exception('Cannot read archives with phar extension');
                    }
                }
            }
            $info = PEAR2_Pyrus_Config::parsePackageName($package);
            return 'PEAR2_Pyrus_Package_Remote';
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Package_Exception('package "' . $package . '" is unknown', $e);
        }
    }
}
