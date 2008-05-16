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
            } else {
                $phar = new PharData($package, RecursiveDirectoryIterator::KEY_AS_FILENAME);
            }
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Package_Phar_Exception('Could not open Phar archive ' .
                $package, $e);
        }
        $package = str_replace('\\', '/', $package);
        $where = (string) PEAR2_Pyrus_Config::current()->temp_dir;
        $where = str_replace('\\', '/', $where);
        $where = str_replace('//', '/', $where);
        $where = str_replace('/', DIRECTORY_SEPARATOR, $where);
        if (!file_exists($where)) {
            mkdir($where, 0777, true);
        }
        $where = realpath($where);
        if (dirname($where . 'a') == $where) {
            $where = substr($where, 0, strlen($where) - 1);
        }
        $this->_tmpdir = $where;
        try {
            $pxml = $phar->getMetaData();
            $phar->extractTo($where);
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Package_Phar_Exception('Could not extract Phar archive ' .
                $package, $e);
        }
        parent::__construct(new PEAR2_Pyrus_PackageFile($where . DIRECTORY_SEPARATOR . $pxml), $parent);
    }

    function __destruct()
    {
        usort(self::$_tempfiles, array('PEAR2_Pyrus_Package_Base', 'sortstuff'));
        foreach (self::$_tempfiles as $fileOrDir) {
            if (!file_exists($fileOrDir)) continue;
            if (is_file($fileOrDir)) {
                unlink($fileOrDir);
            } elseif (is_dir($fileOrDir)) {
                rmdir($fileOrDir);
            }
        }
    }

    private static function _addTempFile($file)
    {
        self::$_tempfiles[] = $file;
    }

    private static function _addTempDirectory($dir)
    {
        do {
            self::$_tempfiles[] = $dir;
            $dir = dirname($dir);
        } while (!file_exists($dir));
    }

    function getLocation()
    {
        return $this->_tmpdir;
    }

    function __get($var)
    {
        if ($var === 'archivefile') {
            return $this->_packagename;
        }
        return parent::__get($var);
    }

    function getFileContents($file, $asstream = false)
    {
        if (!isset($this->packagefile->info->files[$file])) {
            throw new PEAR2_Pyrus_Package_Exception('file ' . $file . ' is not in package.xml');
        }
        $extract = $this->_tmpdir . DIRECTORY_SEPARATOR . $file;
        $extract = str_replace('\\', '/', $extract);
        $extract = str_replace('//', '/', $extract);
        $extract = str_replace('/', DIRECTORY_SEPARATOR, $extract);
        return $asstream ? fopen($extract, 'rb') : file_get_contents($extract);
    }
}
