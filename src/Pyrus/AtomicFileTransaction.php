<?php
/**
 * PEAR2_Pyrus_AtomicFileTransaction
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
 * Atomic file installation infrastructure, guarantees safe installation.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_AtomicFileTransaction
{
    protected $role;
    protected $needsFullBackup;
    protected $rolepath;
    protected $journalpath;
    protected $backuppath;
    protected $intransaction = false;
    protected $defaultMode;

    function __construct(PEAR2_Pyrus_Installer_Role_Common $role, $rolepath)
    {
        $this->role = $role;
        $this->rolepath = $rolepath;
        $this->backuppath = dirname($rolepath) . DIRECTORY_SEPARATOR .
            '.old-' . basename($rolepath);
        $this->journalpath = dirname($rolepath) . DIRECTORY_SEPARATOR .
            '.journal-' . basename($rolepath);
        $this->defaultMode = PEAR2_Pyrus_Config::current()->umask;
    }

    function removePath($relativepath, $strict = true)
    {
        $path = $this->journalpath . DIRECTORY_SEPARATOR . $relativepath;
        if (!file_exists($path)) {
            return;
        }
        if (is_dir($path)) {
            if (!@rmdir($path) && $strict) {
                throw new PEAR2_Pyrus_AtomicFileTransaction_Exception(
                    'Cannot remove directory ' . $path);
            }
        } else {
            if (!@unlink($path) && $strict) {
                throw new PEAR2_Pyrus_AtomicFileTransaction_Exception(
                    'Cannot remove file ' . $path);
            }
        }
    }

    function createOrOpenPath($relativepath, $contents = null, $mode = null)
    {
        if ($mode === null) {
            $mode = $this->defaultMode;
        }
        $path = $this->journalpath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativepath);
        if ($contents) {
            if (is_resource($contents)) {
                $fp = fopen($path, 'wb');
                if (!$fp) {
                    throw new PEAR2_Pyrus_AtomicFileTransaction_Exception('Unable to open ' .
                        $relativepath . ' for writing in ' . $this->journalpath);
                }
                stream_copy_to_stream($fp, $contents);
                fclose($fp);
            } else {
                file_put_contents($path, $contents);
            }
            if ($mode) {
                chmode($path, $mode);
            }
            return $path;
        } else {
            $fp = fopen($path, 'wb');
            if (!$fp) {
                throw new PEAR2_Pyrus_AtomicFileTransaction_Exception('Unable to open ' .
                    $relativepath . ' for writing in ' . $this->journalpath);
            }
            return $fp;
        }
    }

    function rmrf($path)
    {
        $pass = 1;
kill_dirs:
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->rolepath),
                                               RecursiveIteratorIterator::LEAVES_ONLY)
                 as $file) {
            if ($file->getFilename() == '.' || $file->getFilename() == '..') {
                continue;
            }
            if (is_dir($file->getPathname())) {
                if (!rmdir($file->getPathname())) {
                    throw new PEAR2_Pyrus_AtomicFileTransaction_Exception(
                        'Unable to fully remove ' . $path);
                }
            } else {
                if (!unlink($file->getPathname())) {
                    throw new PEAR2_Pyrus_AtomicFileTransaction_Exception(
                        'Unable to fully remove ' . $path);
                }
            }
        }
        if ($pass++ == 1) {
            goto kill_dirs;
        }
        rmdir($path);
    }

    function copyToJournal()
    {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->rolepath),
                                               RecursiveIteratorIterator::LEAVES_ONLY)
                 as $file) {
            if ($file->getFilename() == '.' || $file->getFilename() == '..') {
                continue;
            }
            $time = $file->getMTime();
            $atime = $file->getATime();
            $perms = $file->getPerms();
            $src = str_replace($this->rolepath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            if (!copy($file->getPathName(), $this->journalpath . DIRECTORY_SEPARATOR . $src)) {
                throw new PEAR2_Pyrus_AtomicFileTransaction_Exception(
                    'Unable to complete journal creation for transaction');
            }
            if (!touch($this->journalpath . DIRECTORY_SEPARATOR . $src, $time, $atime)) {
                throw new PEAR2_Pyrus_AtomicFileTransaction_Exception(
                    'Unable to complete journal creation for transaction');
            }
            if (!chmod($this->journalpath . DIRECTORY_SEPARATOR . $src, $perms)) {
                throw new PEAR2_Pyrus_AtomicFileTransaction_Exception(
                    'Unable to complete journal creation for transaction');
            }
        }
    }

    function begin()
    {
        if (!file_exists($this->journalpath)) {
            @mkdir($this->journalpath, 0755, true);
            if (!file_exists($this->journalpath)) {
                throw new PEAR2_Pyrus_AtomicFileTransaction_Exception(
                    'unrecoverable transaction error: cannot create journal path ' . $this->journalpath);
            }
            $this->copyToJournal();
        } elseif (!is_dir($this->journalpath)) {
            throw new PEAR2_Pyrus_AtomicFileTransaction_Exception(
                'unrecoverable transaction error: journal path exists');
        } else {
            $this->rmrf($this->journalpath);
        }
        $this->intransaction = true;
    }

    function rollback()
    {
        $this->intransaction = false;
        $this->rmrf($this->journalpath);
    }

    function commit()
    {
        if (!rename($this->rolepath, $this->backuppath)) {
            $this->intransaction = false;
            throw new PEAR2_Pyrus_AtomicFileTransaction_Exception(
                'CRITICAL - unable to complete transaction, rename of actual to backup path failed');
        }
        // here is the only critical moment - a failure in between these two renames
        // leaves us with no source
        if (!rename($this->journalpath, $this->rolepath)) {
            $this->intransaction = false;
            rename($this->backuppath, $this->rolepath);
            throw new PEAR2_Pyrus_AtomicFileTransaction_Exception(
                'CRITICAL - unable to complete transaction, rename of journal to actual path failed');
        }
        // from here we are good to go
        $this->intransaction = false;
        $this->rmrf($this->backuppath);
    }
}