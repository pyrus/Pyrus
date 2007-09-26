<?php
class PEAR2_Pyrus_Package_Tar implements ArrayAccess, Iterator, PEAR2_Pyrus_IPackage
{
    private $_fp;
    private $_packagename;
    private $_internalFileLength;
    private $_footerLength;
    private $_parent;
    private $_packagefile;
    private $_tmpdir;
    private $_BCpackage;
    static private $_tempfiles = array();

    /**
     * @param string $package path to package file
     */
    function __construct($package, PEAR2_Pyrus_Package $parent)
    {
        $this->_parent = $parent;
        $this->_packagename = $package;
        $info = pathinfo($package);
        $streamfilters = stream_get_filters();
        $this->_fp = fopen($package, 'rb');
        switch ($info['extension']) {
            case 'tgz' :
                if ($this->_fp) {
                    fclose($this->_fp);
                    $this->_fp = gzopen($package, 'rb');
                }
                break;
            case 'tbz' :
                if (!in_array('bzip2.*', $streamfilters)) {
                    throw new PEAR2_Pyrus_Package_Tar_Exception('Cannot open package' .
                        $package . ', bzip2 decompression is not available');
                }
                stream_filter_append($this->_fp, 'bzip2.decompress');
        }
        if (!$this->_fp) {
            throw new PEAR2_Pyrus_Package_Tar_Exception('Cannot open package ' . $package);
        }
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
        usort(self::$_tempfiles, array('PEAR2_Pyrus_Package_Tar', 'sortstuff'));
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

    function offsetExists($offset)
    {
        $this->_extract();
        return $this->_packagefile->offsetExists($offset);
    }

    function offsetGet($offset)
    {
        $this->_extract();
        return $this->_packagefile->offsetGet($offset);
    }

    function offsetSet($offset, $value)
    {
        return;
    }

    function offsetUnset($offset)
    {
        return;
    }

    function current()
    {
        $this->_extract();
        return $this->_packagefile->current();
    }

    function  key()
    {
        return $this->_packagefile->key();
    }

    function  next ()
    {
        $this->_extract();
        $this->_packagefile->next();
    }

    function  rewind()
    {
        $this->_extract();
        $this->_packagefile->rewind();
    }

    function __call($func, $args)
    {
        $this->_extract();
        // delegate to the internal object
        return call_user_func_array(array($this->_packagefile, $func), $args);
    }

    function getLocation()
    {
        $ret = (string) PEAR2_Pyrus_Config::current()->temp_dir;
        // support old packages
        if (file_exists($a = $ret . DIRECTORY_SEPARATOR .
              $this->getPackage() . '-' . $this->getVersion())) {
            $a = str_replace('\\', '/', $a);
            $a = str_replace('//', '/', $a);
            $a = str_replace('/', DIRECTORY_SEPARATOR, $a);
            return $a;
        }
        $ret = str_replace('\\', '/', $ret);
        $ret = str_replace('//', '/', $ret);
        $ret = str_replace('/', DIRECTORY_SEPARATOR, $ret);
        return $ret;
    }

    function __get($var)
    {
        if ($var === 'archivefile') {
            return $this->_packagename;
        }
        $this->_extract();
        return $this->_packagefile->$var;
    }

    function __toString()
    {
        $this->_extract();
        return $this->_packagefile->__toString();
    }

    function getPackageFile()
    {
        $this->_extract();
        return $this->_packagefile->getPackageFile();
    }

    function  valid()
    {
        $this->_extract();
        return $this->_packagefile->valid();
    }

    function getFileContents($file, $asstream = false)
    {
        $this->_extract();
        if (!isset($this->_packagefile->files[$file])) {
            throw new PEAR2_Pyrus_Package_Exception('file ' . $file . ' is not in package.xml');
        }
        if ($this->_BCpackage) {
            // old fashioned PEAR 1.x packages put everything in Package-Version/
            // directory
            $extract = $this->_packagefile->name . '-' .
                $this->_packagefile->version['release'] .
                DIRECTORY_SEPARATOR . $file;
        }
        $extract = $this->_tmpdir . $extract;
        $extract = str_replace('\\', '/', $extract);
        $extract = str_replace('//', '/', $extract);
        $extract = str_replace('/', DIRECTORY_SEPARATOR, $extract);
        return $asstream ? fopen($extract, 'rb') : file_get_contents($extract);
    }

    private function _processHeader($rawHeader)
    {
        if (strlen($rawHeader) < 512 || $rawHeader == pack("a512", "")) {
            throw new PEAR2_Pyrus_Package_Tar_Exception(
                'Error: "' . $this->_packagename . '" has corrupted tar header');
        }

        $header = unpack(
            "a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/".
            "a8checksum/a1type/a100linkname/a6magic/a2version/".
            "a32uname/a32gname/a8devmajor/a8devminor/a155path",
            $rawHeader);
        $this->_internalFileLength = octdec($header['size']);
        if ($this->_internalFileLength % 512 == 0) {
            $this->_footerLength = 0;
        } else {
            $this->_footerLength = 512 - $this->_internalFileLength % 512;
        }
        return $header;
    }

    private function _readHeader($rawHeader)
    {
        if (!strlen($rawHeader)) {
            $this->_internalFileLength = $this->_footerLength = 0;
            return true;
        }

        if (strlen($rawHeader) != 512) {
            throw new PEAR2_Pyrus_Package_Tar_Exception(
                'Invalid block size : ' . strlen($rawHeader));
        }
        $header = $this->_processHeader($rawHeader);
        if ($header['type'] == 'L') {
            // filenames longer than 100 characters
            // borrowed from Archive_Tar written by Vincent Blavet
            $longFilename = '';
            $n = floor($header['size'] / 512);
            for ($i=0; $i < $n; $i++) {
                $content = fread($this->_fp, 512);
                $longFilename .= $content;
            }
            if (($header['size'] % 512) != 0) {
                $content = fread($this->_fp, 512);
                $longFilename .= $content;
            }
            // ----- Read the next header
            $newHeader = fread($this->_fp, 512);
            $header = $this->_processHeader($newHeader);
            $header['filename'] = trim($longFilename);
            $rawHeader = $newHeader;
        }
        if ($this->_maliciousFilename($header['filename'])) {
            throw new PEAR2_Pyrus_Package_Tar_Exception('Malicious .tar detected, file "' .
                $header['filename'] .
                '" will not install in desired directory tree');
        }

        $checksum = 256; // 8 * ord(' ');
        $c1 = str_split($rawHeader);
        $checkheader = array_merge(array_slice($c1, 0, 148), array_slice($c1, 156));
        if (!function_exists('_pear2tarchecksum')) {
            function _pear2tarchecksum($a, $b) {return $a + ord($b);}
        }
        $checksum += array_reduce($checkheader, '_pear2tarchecksum');

        // ----- Extract the checksum
        $header['checksum'] = octdec(trim($header['checksum']));
        if ($header['checksum'] != $checksum) {
            $header['filename'] = '';

            // ----- Look for last block (empty block)
            if (($checksum == 256) && ($header['checksum'] == 0)) {
                return true;
            }

            throw new PEAR2_Pyrus_Package_Tar_Exception(
                'Invalid checksum for header of file "' . $header['filename'] .
	            '" : ' . $checksum . ' calculated, ' .
			    $header['checksum'] . ' expected');
        }

        $header['filename'] = trim($header['filename']);
        $header['mode'] = octdec(trim($header['mode']));
        $header['uid'] = octdec(trim($header['uid']));
        $header['gid'] = octdec(trim($header['gid']));
        $header['size'] = octdec(trim($header['size']));
        $header['mtime'] = octdec(trim($header['mtime']));
        if ($header['type'] == '5') {
            $header['size'] = 0;
        }
        $header['linkname'] = trim($header['linkname']);
        return $header;
    }

    /**
     * Detect and report a malicious file name
     *
     * @param string $file
     * @return bool
     * @access private
     */
    private function _maliciousFilename($file)
    {
        if (strpos($file, '/../') !== false) {
            return true;
        }
        if (strpos($file, '../') === 0) {
            return true;
        }
        return false;
    }

    /**
     * Extract the archive so we can work with the contents
     *
     */
    private function _extract()
    {
        if (isset($this->_packagefile)) {
            return;
        }
        $packagexml = false;
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
        do {
            $header = fread($this->_fp, 512);
            if ($header == pack('a512', '')) {
                // end of archive
                break;
            }
            $header = $this->_readHeader($header);
            $extract = $where . $header['filename'];
            $extract = str_replace('\\', '/', $extract);
            $extract = str_replace('//', '/', $extract);
            $extract = str_replace('/', DIRECTORY_SEPARATOR, $extract);
            self::_addTempFile($extract);
            if (!file_exists(dirname($extract))) {
                self::_addTempDirectory(dirname($extract));
                mkdir(dirname($extract), 0777, true);
            }
            $fp = fopen($extract, 'wb');
            $amount = stream_copy_to_stream($this->_fp, $fp, $this->_internalFileLength);
            if ($amount != $this->_internalFileLength) {
                throw new PEAR2_Pyrus_Package_Tar_Exception(
                    'Unable to fully extract ' . $header['filename'] . ' from ' .
                    $this->_packagename);
            }
            if ($this->_footerLength) {
                fseek($this->_fp, $this->_footerLength, SEEK_CUR);
            }
            if (!$packagexml) {
                if (preg_match('/^package\-.+\-\\d+(?:\.\d+)*(?:[a-zA-Z]+\d*)?.xml$/',
                      $header['filename'])) {
                    $this->_BCpackage = false;
                    $packagexml = $where . $header['filename'];
                } elseif ($header['filename'] == 'package2.xml') {
                    $this->_BCpackage = true;
                    $packagexml = $where . $header['filename'];
                } elseif ($header['filename'] == 'package.xml') {
                    $this->_BCpackage = true;
                    $packagexml = $where . $header['filename'];
                }
            }
        } while ($this->_internalFileLength);
        if (!$packagexml) {
            throw new PEAR2_Pyrus_Package_Tar_Exception('Archive ' . $this->_packagename .
                ' does not contain a package.xml file');
        }
        $this->_packagefile = new PEAR2_Pyrus_Package_Xml($packagexml, $this->_parent);
    }
}