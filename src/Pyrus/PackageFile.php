<?php
/**
 * \pear2\Pyrus\PackageFile
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
 * Base class for a PEAR2 package file
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace pear2\Pyrus;
class PackageFile implements PackageFileInterface
{
    public $info;
    public $path;
    function __construct($package, $class = 'pear2\Pyrus\PackageFile\v2', $isString = false)
    {
        if ($package instanceof PackageFileInterface) {
            $this->path = $package->getFilePath();
            return $this->info = $package;
        }

        $this->path = $package;
        $parser = new PackageFile\Parser\v2;
        if ($isString) {
            $data = $package;
        } else {
            $data = file_exists($package) ? file_get_contents($package) : false;
        }

        if ($data === false || empty($data)) {
            throw new PackageFile\Exception('Unable to open package xml file '
                . $package . ' or file was empty.');
        }

        $this->info = $parser->parse($data, $package, $class);
    }

    function __toString()
    {
        return $this->info->__toString();
    }

    function getValidator()
    {
        return $this->info->getValidator();
    }

    function getPackageFileObject()
    {
        return $this->info;
    }

    function __get($var)
    {
        return $this->info->__get($var);
    }

    function __set($var, $value)
    {
        return $this->info->__set($var, $value);
    }

    function __call($func, $args)
    {
        return call_user_func_array(array($this->info, $func), $args);
    }

    function toArray($forpackaging = false)
    {
        return $this->info->toArray($forpackaging);
    }
}