<?php
/**
 * \pear2\Pyrus\Package\Zip
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
namespace pear2\Pyrus\Package;
class Zip extends \pear2\Pyrus\Package\Base
{
    static private $_tempfiles = array();
    private $_tmpdir;

    /**
     * @param string $package path to package file
     */
    function __construct($package, \pear2\Pyrus\Package $parent)
    {
        if (!class_exists('ZIPArchive')) {
            throw new Zip\Exception('Zip extension is not available');
        }

        $this->archive = $package;
        $zip = new ZIPArchive;
        if (true !== ($zip->open($package))) {
            throw new Zip\Exception('Could not open ZIP archive ' . $package);
        }

        if (false !== ($pxml = $zip->getNameIndex(0))) {
            if (!preg_match('/^package\-.+\-\\d+(?:\.\d+)*(?:[a-zA-Z]+\d*)?.xml$/',
                      $pxml)) {
                throw new Zip\Exception('First file in ZIP archive is not package.xml, cannot process');
            }
        }

        $where = (string) \pear2\Pyrus\Config::current()->temp_dir;
        $where = str_replace(array('\\', '//'), DIRECTORY_SEPARATOR, $where);
        if (!file_exists($where)) {
            mkdir($where, 0777, true);
        }

        $where = realpath($where);
        if (dirname($where . 'a') != $where) {
            $where .= DIRECTORY_SEPARATOR;
        }

        $this->_tmpdir = $where;
        $zip->extractTo($where);
        parent::__construct(new \pear2\Pyrus\PackageFile($where . DIRECTORY_SEPARATOR . $pxml),
            $parent);
    }

    function __destruct()
    {
        usort(self::$_tempfiles, array('pear2\Pyrus\Package\Base', 'sortstuff'));
        foreach (self::$_tempfiles as $fileOrDir) {
            if (!file_exists($fileOrDir)) {
                continue;
            }

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

    function getFilePath($file)
    {
        if (!isset($this->packagefile->info->files[$file])) {
            throw new Exception('file ' . $file . ' is not in package.xml');
        }

        $extract = $this->_tmpdir . DIRECTORY_SEPARATOR . $file;
        $extract = str_replace(array('\\', '//'), DIRECTORY_SEPARATOR, $extract);
        return $extract;
    }
}
