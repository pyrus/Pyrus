<?php
/**
 * \pear2\Pyrus\Package\Cloner
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2009 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/PEAR2/Pyrus/
 */

/**
 * Class for cloning (exact copies with hash intact if possible) packages
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2009 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/PEAR2/Pyrus/
 */
namespace pear2\Pyrus\Package;
class Cloner
{
    protected $hash;
    protected $signature_algo;
    protected $file;
    protected $outfile = false;
    protected $phar;
    protected $tar;
    protected $tgz;
    protected $zip;
    protected $ext;

    function __construct($base, $outdirectory = null)
    {
        try {
            $p = new \PharData($base);
        } catch (\Exception $e) {
            try {
                $p = new \Phar($base);
            } catch (\Exception $ee) {
                throw $e;
            }
        }
        $this->phar = $p;
        if ($p->isFileFormat(\Phar::PHAR)) {
            $this->phar = $p;
        } elseif ($p->isFileFormat(\Phar::TAR) && !$p->isCompressed()) {
            $this->tar = $p;
        } elseif ($p->isFileFormat(\Phar::ZIP)) {
            $this->zip = $p;
        }
        $sig = $p->getSignature();
        if ($sig) {
            $this->hash = $sig['hash'];
            $this->signature_algo = $sig['hash_type'];
        }
        $info = pathinfo($base);
        $this->file = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'];
        $this->ext = substr($info['filename'], strpos($info['filename'], '.'));
        if ($outdirectory) {
            $this->outfile = realpath($outdirectory) . DIRECTORY_SEPARATOR .
                $info['filename'];
            copy($base, $this->outfile . '.' . $info['extension']);
        }
    }

    function toTgz()
    {
        if (isset($this->tgz)) {
            return;
        }
        if (isset($this->tar)) {
            if (file_exists($this->file . '.tgz')) {
                try {
                    $p = new \PharData($this->file . '.tgz');
                } catch (\Exception $e) {
                }
                if ($p->getSignature() === $this->tar->getSignature()) {
                    $this->tgz = $p;
                    if ($this->outfile) {
                        copy($this->file . '.tgz', $this->outfile . '.tgz');
                    }
                    return;
                }
                unset($p);
                \Phar::unlinkArchive($this->file . '.tgz');
            }
            $fp = fopen($this->file . '.tar', 'rb');
            $gp = gzopen($this->file . '.tgz', 'wb');
            stream_copy_to_stream($fp, $gp);
            fclose($fp);
            fclose($gp);
            $this->tgz = new \PharData($this->file . '.tgz');
            if ($this->outfile) {
                copy($this->file . '.tgz', $this->outfile . '.tgz');
            }
            return;
        }
        // by process of elimination, the phar is in zip format
        if (file_exists($this->file . '.tgz')) {
            \Phar::unlinkArchive($this->file . '.tgz');
            unlink($this->file . '.tgz');
        }
        $this->tgz = $this->zip->convertToData(\Phar::TAR, \Phar::GZ, $this->ext . '.tgz');
        if ($this->outfile) {
            copy($this->file . '.tgz', $this->outfile . '.tgz');
        }
        $this->zip = new \PharData($this->file . '.zip');
    }

    function toTar()
    {
        if (isset($this->tar)) {
            return;
        }
        if (isset($this->tgz)) {
            if (file_exists($this->file . '.tar')) {
                try {
                    $p = new \PharData($this->file . '.tar');
                } catch (\Exception $e) {
                }
                if ($p->getSignature() === $this->phar->getSignature()) {
                    $this->tar = $p;
                    if ($this->outfile) {
                        copy($this->file . '.tar', $this->outfile . '.tar');
                    }
                    return;
                }
                unset($p);
                \Phar::unlinkArchive($this->file . '.tar');
            }
            $fp = gzopen($this->file . '.tgz', 'rb');
            $gp = fopen($this->file . '.tar', 'wb');
            stream_copy_to_stream($fp, $gp);
            fclose($fp);
            fclose($gp);
            $this->tar = new \PharData($this->file . '.tar');
            return;
        }
        if (isset($this->zip)) {
            if (file_exists($this->file . '.tar')) {
                \Phar::unlinkArchive($this->file . '.tar');
                unlink($this->file . '.tar');
            }
            $this->tar = $this->zip->convertToData(\Phar::TAR, \Phar::NONE, $this->ext . '.tar');
            $this->tar->setSignatureAlgorithm(\Phar::SHA1);
            if ($this->outfile) {
                copy($this->file . '.tar', $this->outfile . '.tar');
            }
            $this->zip = new \PharData($this->file . '.zip');
            return;
        }
        // by process of elimination, the phar is in phar format
        if (file_exists($this->file . '.tar')) {
            \Phar::unlinkArchive($this->file . '.tar');
            unlink($this->file . '.tar');
        }
        $this->tar = $this->phar->convertToData(\Phar::TAR, \Phar::NONE, $this->ext . '.tar');
        $this->tar->setSignatureAlgorithm(\Phar::SHA1);
        if ($this->outfile) {
            copy($this->file . '.tar', $this->outfile . '.tar');
        }
        $this->phar = new \Phar($this->file . '.phar');
    }

    function toZip()
    {
        if (isset($this->zip)) {
            return;
        }
        if (file_exists($this->file . '.zip')) {
            try {
                $p = new \PharData($this->file . '.zip');
            } catch (\Exception $e) {
            }
            if ($p->getMetadata() && is_string($p->getMetadata()) && isset($p[$p->getMetadata()])) {
                if (isset($this->tar)) {
                    $test = $this->tar;
                } elseif (isset($this->tgz)) {
                    $test = $this->tgz;
                } else {
                    $test = $this->phar;
                }
                if ($p->getMetadata() === $test->getMetadata()) {
                    try {
                        // do both store the same package.xml and are they
                        // identical package.xml?
                        if ($p[$p->getMetadata()]->getContent() == $test[$p->getMetadata()]->getContent()) {
                            $this->zip = $p; // yes
                            if ($this->outfile) {
                                copy($this->file . '.zip', $this->outfile . '.zip');
                            }
                            return;
                        }
                    } catch (\Exception $e) {
                        
                    }
                }
            }
            unset($p);
            \Phar::unlinkArchive($this->file . '.zip');
        }
        if (isset($this->tar)) {
            if (file_exists($this->file . '.zip')) {
                \Phar::unlinkArchive($this->file . '.zip');
                unlink($this->file . '.zip');
            }
            $this->zip = $this->tar->convertToData(\Phar::ZIP, \Phar::NONE, $this->ext . '.zip');
            if ($this->outfile) {
                copy($this->file . '.zip', $this->outfile . '.zip');
            }
            $this->tar = new \PharData($this->file . '.tar');
            return;
        }
        if (isset($this->tgz)) {
            if (file_exists($this->file . '.zip')) {
                \Phar::unlinkArchive($this->file . '.zip');
                unlink($this->file . '.zip');
            }
            $this->zip = $this->tgz->convertToData(\Phar::ZIP, \Phar::NONE, $this->ext . '.zip');
            if ($this->outfile) {
                copy($this->file . '.zip', $this->outfile . '.zip');
            }
            $this->tgz = new \PharData($this->file . '.tgz');
            return;
        }
        // by process of elimination, the phar is in phar format
        if (file_exists($this->file . '.zip')) {
            \Phar::unlinkArchive($this->file . '.zip');
            unlink($this->file . '.zip');
        }
        $this->zip = $this->phar->convertToData(\Phar::ZIP, \Phar::NONE, $this->ext . '.zip');
        if ($this->outfile) {
            copy($this->file . '.zip', $this->outfile . '.zip');
        }
        $this->phar = new \Phar($this->file . '.phar');
    }

    function toPhar()
    {
        if (isset($this->phar)) {
            return;
        }
        if (file_exists($this->file . '.phar')) {
            try {
                $p = new \Phar($this->file . '.phar');
            } catch (\Exception $e) {
            }
            if ($p->getSignature() === $this->phar->getSignature()) {
                $this->phar = $p;
                if ($this->outfile) {
                    copy($this->file . '.phar', $this->outfile . '.phar');
                }
                return;
            }
            unset($p);
            \Phar::unlinkArchive($this->file . '.phar');
        }
        if (isset($this->tar)) {
            if ($this->signature_algo == \Phar::OPENSSL) {
                throw new Exception('Cannot create tar archive, signature is OpenSSL, ' .
                                    'you must directly create it using the package command');
            }
            if (file_exists($this->file . '.phar')) {
                \Phar::unlinkArchive($this->file . '.phar');
                unlink($this->file . '.phar');
            }
            $this->phar = $this->tar->convertToExecutable(\Phar::PHAR, \Phar::NONE, $this->ext . '.phar');
            if ($this->outfile) {
                copy($this->file . '.phar', $this->outfile . '.phar');
            }
            $this->tar = new \PharData($this->file . '.tar');
            return;
        }
        if (isset($this->tgz)) {
            if ($this->signature_algo == \Phar::OPENSSL) {
                throw new Exception('Cannot create tar archive, signature is OpenSSL, ' .
                                    'you must directly create it using the package command');
            }
            if (file_exists($this->file . '.phar')) {
                \Phar::unlinkArchive($this->file . '.phar');
                unlink($this->file . '.phar');
            }
            $this->phar = $this->tar->convertToExecutable(\Phar::PHAR, \Phar::NONE, $this->ext . '.phar');
            if ($this->outfile) {
                copy($this->file . '.phar', $this->outfile . '.phar');
            }
            $this->tgz = new \PharData($this->file . '.tgz');
            return;
        }
        // by process of elimination, the phar is in zip format
        if (file_exists($this->file . '.phar')) {
            \Phar::unlinkArchive($this->file . '.phar');
            unlink($this->file . '.phar');
        }
        $this->phar = $this->tar->convertToExecutable(\Phar::PHAR, \Phar::NONE, $this->ext . '.phar');
        if ($this->outfile) {
            copy($this->file . '.phar', $this->outfile . '.phar');
        }
        $this->zip = new \PharData($this->file . '.zip');
    }
}

