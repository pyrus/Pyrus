<?php
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
            $phar = new Phar($package, RecursiveDirectoryIterator::KEY_AS_FILENAME);
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Package_Phar_Exception('Could not open Phar archive ' .
                $package, $e);
        }
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
        $pxml = $phar->getMetaData();
        foreach (new RecursiveIteratorIterator($phar) as $path => $info) {
            if (strpos(PHP_OS, 'WIN') !== false) {
                $makepath = $where .
                    str_replace('phar:///' . $package, '', $info->getPath());
                $makepath = str_replace('/', '\\', $makepath);
            } else {
                $makepath = $where .
                    str_replace('phar://' . $package, '', $info->getPath());
            }
            if (dirname($makepath . 'a') != $makepath) {
                $makepath .= DIRECTORY_SEPARATOR;
            }
            if (!file_exists($makepath)) {
                mkdir($makepath, 0755, true);
                var_dump($makepath);exit;
            }
            file_put_contents($makepath . $info->getFilename(), fopen($info->getPathName(), 'rb'));
        }
        parent::__construct(new PEAR2_Pyrus_PackageFile($where . DIRECTORY_SEPARATOR . $pxml), $parent);
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
        usort(self::$_tempfiles, array('PEAR2_Pyrus_Package_Phar', 'sortstuff'));
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