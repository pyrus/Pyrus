<?php
/**
 * PEAR2_Pyrus_Package_Zip
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
 * Class representing a Zip package
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Package_Zip extends PEAR2_Pyrus_Package_Base
{
    private $_packagename;
    static private $_tempfiles = array();
    private $_tmpdir;

    /**
     * @param string $package path to package file
     */
    function __construct($package, PEAR2_Pyrus_Package $parent)
    {
        if (!class_exists('ZIPArchive')) {
            throw new PEAR2_Pyrus_Package_Zip_Exception(
                'Zip extension is not available');
        }
        $this->_packagename = $package;
        $zip = new ZIPArchive;
        if (true !== ($zip->open($package))) {
            throw new PEAR2_Pyrus_Package_Zip_Exception('Could not open ZIP archive ' .
                $package);
        }
        if (false !== ($pxml = $zip->getNameIndex(0))) {
            if (!preg_match('/^package\-.+\-\\d+(?:\.\d+)*(?:[a-zA-Z]+\d*)?.xml$/',
                      $pxml)) {
                throw new PEAR2_Pyrus_Package_Zip_Exception('First file in ZIP archive ' .
                    'is not package.xml, cannot process');
            }
        }
        $where = (string) PEAR2_Pyrus_Config::current()->temp_dir;
        $where = str_replace('\\', '/', $where);
        $where = str_replace('//', '/', $where);
        $where = str_replace('/', DIRECTORY_SEPARATOR, $where);
        if (!file_exists($where)) {
            mkdir($where, 0777, true);
        }
        $where = realpath($where);
        if (dirname($where . 'a') != $where) {
            $where .= DIRECTORY_SEPARATOR;
        }
        $this->_tmpdir = $where;
        $zip->extractTo($where);
        parent::__construct(new PEAR2_Pyrus_PackageFile($where . DIRECTORY_SEPARATOR . $pxml),
            $parent);
    }

    /**
     * Sort files/directories for removal
     *
     * Files are always removed first, followed by directories in
     * path order
     * @param unknown_type $a
     * @param unknown_type $b
     * @return unknown
     */
    static function sortstuff($a, $b)
    {
        // files can be removed in any order
        if (is_file($a) && is_file($b)) return 0;
        if (is_dir($a) && is_file($b)) return 1;
        if (is_dir($b) && is_file($a)) return -1;
        $countslasha = substr_count($a, DIRECTORY_SEPARATOR);
        $countslashb = substr_count($b, DIRECTORY_SEPARATOR);
        if ($countslasha > $countslashb) return -1;
        if ($countslashb > $countslasha) return 1;
        // if not subdirectories, tehy can be removed in any order
        return 0;
    }

    function __destruct()
    {
        usort(self::$_tempfiles, array('PEAR2_Pyrus_Package_Zip', 'sortstuff'));
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