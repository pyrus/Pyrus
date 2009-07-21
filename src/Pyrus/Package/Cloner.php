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
    protected $phar;
    protected $tar;
    protected $tgz;
    protected $zip;
    protected $ext;

    function __construct(\pear2\Pyrus\Package $base)
    {
        try {
            $p = new PharData($base);
        } catch (\Exception $e) {
            try {
                $p = new Phar($base);
            } catch (\Exception $ee) {
                throw $e;
            }
        }
        $this->phar = $p;
        if ($p->isFileFormat(Phar::PHAR)) {
            $this->phar = $p;
        } elseif ($p->isFileFormat(Phar::TAR)) {
            $this->tar = $p;
        } elseif ($p->isFileFormat(Phar::ZIP)) {
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
    }

    function toTgz()
    {
        if (isset($this->tgz)) {
            return;
        }
        if (isset($this->tar)) {
            if (file_exists($this->file . '.tgz')) {
                try {
                    $p = new PharData($this->file . '.tgz');
                } catch (\Exception $e) {
                }
                if ($p->getSignature() === $this->tar->getSignature()) {
                    $this->tgz = $p;
                    return;
                }
                unset($p);
                Phar::unlinkArchive($this->file . '.tgz');
            }
            $fp = fopen($this->file . '.tar', 'rb');
            $gp = gzopen($this->file . '.tgz', 'wb');
            stream_copy_to_stream($fp, $gp);
            fclose($fp);
            fclose($gp);
            $this->tgz = new PharData($this->file . '.tgz');
            return;
        }
        // by process of elimination, the phar is in zip format
        $this->tgz = $this->zip->convertToData(Phar::TAR, Phar::GZ, $this->ext . '.tgz');
        $this->zip = new PharData($this->file . '.zip');
    }

    function toTar()
    {
        if (isset($this->tar)) {
            return;
        }
        if (isset($this->tgz)) {
            if (file_exists($this->file . '.tar')) {
                try {
                    $p = new PharData($this->file . '.tar');
                } catch (\Exception $e) {
                }
                if ($p->getSignature() === $this->phar->getSignature()) {
                    $this->tar = $p;
                    return;
                }
                unset($p);
                Phar::unlinkArchive($this->file . '.tar');
            }
            $fp = gzopen($this->file . '.tgz', 'rb');
            $gp = fopen($this->file . '.tar', 'wb');
            stream_copy_to_stream($fp, $gp);
            fclose($fp);
            fclose($gp);
            $this->tar = new PharData($this->file . '.tar');
            return;
        }
        if (isset($this->zip)) {
            $this->tar = $this->zip->convertToData(Phar::TAR, Phar::NONE, $this->ext . '.tar');
            $this->zip = new PharData($this->file . '.zip');
        }
        // by process of elimination, the phar is in phar format
        $this->tar = $this->phar->convertToData(Phar::TAR, Phar::NONE, $this->ext . '.tar');
        $this->phar = new Phar($this->file . '.phar');
    }

    function toZip()
    {
        if (isset($this->zip)) {
            return;
        }
        if (file_exists($this->file . '.zip')) {
            try {
                $p = new PharData($this->file . '.zip');
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
                            return;
                        }
                    } catch (\Exception $e) {
                        
                    }
                }
            }
            unset($p);
            Phar::unlinkArchive($this->file . '.zip');
        }
        if (isset($this->tar)) {
            $this->zip = $this->tar->convertToData(Phar::ZIP, Phar::NONE, $this->ext . '.zip');
            $this->tar = new PharData($this->file . '.tar');
        }
        if (isset($this->tgz)) {
            $this->zip = $this->zip->convertToData(Phar::ZIP, Phar::NONE, $this->ext . '.zip');
            $this->tgz = new PharData($this->file . '.tgz');
        }
        // by process of elimination, the phar is in phar format
        $this->zip = $this->phar->convertToData(Phar::ZIP, Phar::NONE, $this->ext . '.zip');
        $this->phar = new Phar($this->file . '.phar');
    }

    function toPhar()
    {
        if (isset($this->phar)) {
            return;
        }
        if (file_exists($this->file . '.phar')) {
            try {
                $p = new Phar($this->file . '.phar');
            } catch (\Exception $e) {
            }
            if ($p->getSignature() === $this->phar->getSignature()) {
                $this->phar = $p;
                return;
            }
            unset($p);
            Phar::unlinkArchive($this->file . '.phar');
        }
        if (isset($this->tar)) {
            if ($this->signature_algo == Phar::OPENSSL) {
                throw new Exception('Cannot create tar archive, signature is OpenSSL, ' .
                                    'you must directly create it using the package command');
            }
            $this->phar = $this->tar->convertToExecutable(Phar::PHAR, Phar::NONE, $this->ext . '.phar');
            $this->tar = new PharData($this->file . '.tar');
        }
        if (isset($this->tgz)) {
            if ($this->signature_algo == Phar::OPENSSL) {
                throw new Exception('Cannot create tar archive, signature is OpenSSL, ' .
                                    'you must directly create it using the package command');
            }
            $this->phar = $this->tar->convertToExecutable(Phar::PHAR, Phar::NONE, $this->ext . '.phar');
            $this->tgz = new PharData($this->file . '.tgz');
        }
        // by process of elimination, the phar is in zip format
        $this->phar = $this->tar->convertToExecutable(Phar::PHAR, Phar::NONE, $this->ext . '.phar');
        $this->zip = new PharData($this->file . '.zip');
    }
}

