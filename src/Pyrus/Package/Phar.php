<?php
/**
 * PEAR2_Pyrus_Package_Phar
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/PEAR2/Pyrus/
 */

/**
 * Class for phar packages
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/PEAR2/Pyrus/
 */
class PEAR2_Pyrus_Package_Phar extends PEAR2_Pyrus_Package_Base
{
    private $_packagename;
    static private $_tempfiles = array();
    private $_tmpdir;
    private $_BCpackage = false;

    /**
     * @param string $package path to package file
     */
    function __construct($package, PEAR2_Pyrus_Package $parent)
    {
        $package = realpath($package);
        if (!$package) {
            throw new PEAR2_Pyrus_Package_Phar_Exception(
                'Phar package ' . $package . ' does not exist');
        }

        if (!class_exists('Phar')) {
            throw new PEAR2_Pyrus_Package_Phar_Exception(
                'Phar extension is not available');
        }

        $this->_packagename = $package;
        try {
            if (Phar::isValidPharFilename($package, 1)) {
                $phar = new Phar($package, RecursiveDirectoryIterator::KEY_AS_FILENAME);
                $pxml = $phar->getMetaData();
            } else {
                $phar = new PharData($package, RecursiveDirectoryIterator::KEY_AS_FILENAME);
                $pxml = false;
            }
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Package_Phar_Exception('Could not open Phar archive ' .
                $package, $e);
        }

        $package = str_replace('\\', '/', $package);
        try {
            if ($pxml === false) {
                foreach (new RecursiveIteratorIterator($phar,
                            RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
                    $filename = $file->getFileName();
                    // default to new package.xml, but search for old one
                    // also
                    if (preg_match('@^(.+)\-package.xml$@', $filename)) {
                        $pxml = $file->getPathName();
                        break;
                    } elseif ($filename == 'package2.xml') {
                        $this->_BCpackage = true;
                        $pxml = $file->getPathName();
                    } elseif ($pxml === false && $filename == 'package.xml') {
                        $this->_BCpackage = true;
                        $pxml = $file->getPathName();
                    }
                }
            }
            if ($pxml === false) {
                throw new PEAR2_Pyrus_Package_Phar_Exception('No package.xml in archive');
            }
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Package_Phar_Exception('Could not extract Phar archive ' .
                $package, $e);
        }

        parent::__construct(new PEAR2_Pyrus_PackageFile($pxml,
                                                       'PEAR2_Pyrus_PackageFile_v2'),
                            $parent);
    }

    function getTarballPath()
    {
        return $this->_packagename;
    }

    function isNewPackage()
    {
        return !$this->_BCpackage;
    }

    function __get($var)
    {
        if ($var === 'archivefile') {
            return $this->_packagename;
        }

        return parent::__get($var);
    }

    function getFilePath($file)
    {
        if (!isset($this->packagefile->info->files[$file])) {
            throw new PEAR2_Pyrus_Package_Exception('file ' . $file . ' is not in package.xml');
        }
        
        $phar_file = 'phar://' . str_replace('\\', '/', $this->_packagename) . '/' . $file;
        if (!file_exists($phar_file)) {
            $phar_file = 'phar://' . str_replace('\\', '/', $this->_packagename) . '/' .
                    $this->packagefile->info->name . '-' .
                    $this->packagefile->info->version['release'] . '/' .
                    $file;
        
        }
        return $phar_file;
    }
}
