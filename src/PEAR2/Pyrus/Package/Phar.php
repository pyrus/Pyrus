<?php
/**
 * \Pyrus\Package\Phar
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/PEAR2/Pyrus/
 */

/**
 * Class for phar packages
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/PEAR2/Pyrus/
 */
namespace Pyrus\Package;
class Phar extends \Pyrus\Package\Base
{
    static private $_tempfiles = array();
    private $_tmpdir;
    private $_BCpackage = false;

    /**
     * @param string $package path to package file
     */
    function __construct($package, \Pyrus\Package $parent)
    {
        $package = realpath($package);
        if (!$package) {
            throw new Phar\Exception('Phar package ' . $package . ' does not exist');
        }

        $pxml = false;
        $this->archive = $package;
        try {
            if (\Phar::isValidPharFilename($package, 1)) {
                $phar = new \Phar($package, \RecursiveDirectoryIterator::KEY_AS_FILENAME);
                $pxml = 'phar://' . $package . '/' . $phar->getMetaData();
            } else {
                $phar = new \PharData($package, \RecursiveDirectoryIterator::KEY_AS_FILENAME);
                if ($phar->getMetaData()) {
                    $pxml = 'phar://' . $package . '/' . $phar->getMetaData();
                }
            }
        } catch (\Exception $e) {
            throw new Phar\Exception('Could not open Phar archive ' . $package, $e);
        }

        $package = str_replace('\\', '/', $package);
        try {
            if ($pxml === false) {
                $info = pathinfo($package);
                $internal = $info['filename'];
                if (isset($phar[$internal . '/.xmlregistry'])) {
                    if ($phar instanceof \PharData) {
                        $iterate = new \PharData('phar://' . $package . '/' . $internal . '/.xmlregistry');
                    } else {
                        $iterate = new \Phar('phar://' . $package . '/' . $internal . '/.xmlregistry');
                    }

                    foreach (new \RecursiveIteratorIterator($iterate,
                                \RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
                        $filename = $file->getFileName();
                        // default to new package.xml
                        if (preg_match('@^(.+)\-package.xml$@', $filename)) {
                            $pxml = $file->getPathName();
                            break;
                        }
                    }
                } else {
                    foreach (array('package2.xml',
                                   $internal . '/' . 'package2.xml',
                                   'package.xml',
                                   $internal . '/' . 'package.xml') as $checkfile) {
                        if (isset($phar[$checkfile])) {
                            $this->_BCpackage = true;
                            $pxml = $phar[$checkfile]->getPathName();
                            break;
                        }
                    }
                }
            }

            if ($pxml === false) {
                throw new Phar\Exception('No package.xml in archive');
            }
        } catch (\Exception $e) {
            throw new Phar\Exception('Could not extract Phar archive ' .
                $package, $e);
        }

        parent::__construct(new \Pyrus\PackageFile($pxml,
                                                       'Pyrus\PackageFile\v2'),
                            $parent);
    }

    function getTarballPath()
    {
        return $this->archive;
    }

    function copyTo($where)
    {
        $location = $where . DIRECTORY_SEPARATOR . basename($this->archive);
        copy($this->archive, $location);
        $this->archive = $location;
    }

    function isNewPackage()
    {
        return !$this->_BCpackage;
    }

    function getFilePath($file)
    {
        if (!isset($this->packagefile->info->files[$file])) {
            throw new Exception('file ' . $file . ' is not in package.xml');
        }

        $archive = str_replace('\\', '/', $this->archive);
        $file = str_replace('\\', '/', $file);
        $phar    = 'phar://' . $archive . '/' . $file;
        if (!file_exists($phar)) {
            $phar = 'phar://' . $archive . '/' . $this->packagefile->info->name .
                    '-' . $this->packagefile->info->version['release'] . '/' . $file;
        }

        return $phar;
    }
}