<?php
/**
 * \Pyrus\Package
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
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus;
class Package implements \Pyrus\PackageInterface
{
    /**
     * The actual package representation
     *
     * @var \Pyrus\Package\Xml|\Pyrus\Package\Phar
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

        list($class, $packagedescription, $depgroup) = self::parsePackageDescription($packagedescription, $forceremote);
        $this->internal = new $class($packagedescription, $this);
        if ($depgroup) {
            $this->internal->requestedGroup = $depgroup;
        }
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

    function setInternalPackage(PackageInterface $internal)
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
        return $this->internal instanceof Package\Remote ||
                    $this->internal instanceof Channel\RemotePackage || (
                    $this->internal instanceof Package && $this->internal->isRemote());
    }

    function download()
    {
        if ($this->internal instanceof Package\Remote) {
            $this->internal = $this->internal->download();
        }
    }

    function copyTo($where)
    {
        $this->internal->copyTo($where);
    }

    public static function parsePackageDescription($package, $forceremote = false)
    {
        if (strpos($package, 'http://') === 0 || strpos($package, 'https://') === 0) {

            if (substr($package, -4) == '.xml') {
                // remote package.xml file
                return array('Pyrus\Package\Xml', $package, false);
            }

            return array('Pyrus\Package\Remote', $package, false);
        }

        try {
            $test = parse_url($package);
            $depgroup = false;
            if (!$forceremote && count($test) == 2 && isset($test['fragment']) && isset($test['path'])) {
                // local path with dependency group?
                if (file_exists($test['path'])) {
                    // yes
                    $package = $test['path'];
                    $depgroup = $test['fragment'];
                }
            }

            if (!$forceremote && @file_exists($package) && @is_file($package)) {
                $info = pathinfo($package);
                if (!isset($info['extension']) || !strlen($info['extension'])) {
                    // guess based on first 5 characters
                    $f = @fopen($package, 'rb');
                    if ($f) {
                        $first5 = fread($f, 5);
                        fclose($f);
                        if ($first5 == '<?xml') {
                            return array('Pyrus\Package\Xml', $package, $depgroup);
                        }

                        return array('Pyrus\Package\Phar', $package, $depgroup);
                    }
                } else {
                    if (extension_loaded('phar') && strtolower($info['extension']) != 'xml') {
                        return array('Pyrus\Package\Phar', $package, $depgroup);
                    }

                    switch (strtolower($info['extension'])) {
                        case 'xml' :
                            return array('Pyrus\Package\Xml', $package, $depgroup);
                        default:
                            throw new Package\Exception('Cannot read archives with phar extension');
                    }
                }
            }

            $info = Config::parsePackageName($package);
            return array('Pyrus\Package\Remote', $package, $depgroup);
        } catch (\Exception $e) {
            throw new Package\Exception('package "' . $package . '" is unknown', $e);
        }
    }
}
