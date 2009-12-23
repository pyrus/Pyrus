<?php
/**
 * AtomicFileTransaction
 *
 * This class implements a simple, nearly atomic way of handling file installation
 * transaction similar to the way a database handles a query transaction.
 *
 * To use, first call {@link AtomicFileTransaction::begin()} and
 * then remove old files with {@link AtomicFileTransaction::removePath()}
 * or install new files with {@link AtomicFileTransaction::createOrOpenPath()}.
 *
 * To abort, use {@link AtomicFileTransaction::rollback()}, and to
 * finish, use {@link AtomicFileTransaction::commit()} followed by
 * {@link AtomicFileTransaction::removeBackups()}.
 *
 * The separation of backup removal from committing allows attempting to modify the
 * registry in between file installation and removal of backups, so that in the
 * worst case, it is easy to completely roll back the file installation without
 * any loss of information, even after the process has terminated.
 *
 * To repair a broken commit or rollback, use
 * {@link AtomicFileTransaction::repair()}.
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
namespace pear2\Pyrus;
use \pear2\MultiErrors;
class AtomicFileTransaction
{
    static protected $allTransactObjects = array();
    static protected $intransaction = false;
    static protected $inrepair = false;
    protected $rolepath;
    protected $journalpath;
    protected $backuppath;
    protected $defaultMode;
    protected $committed = false;

    protected function __construct($rolepath)
    {
        if (isset(Main::$options['packagingroot'])) {
            $rolepath = Main::prepend(Main::$options['packagingroot'], $rolepath);
        }
        $this->rolepath = $rolepath;
        $this->backuppath = dirname($rolepath) . DIRECTORY_SEPARATOR .
            '.old-' . basename($rolepath);
        $this->journalpath = dirname($rolepath) . DIRECTORY_SEPARATOR .
            '.journal-' . basename($rolepath);
        $this->defaultMode = 0777 & ~octdec(Config::current()->umask);
    }

    /**
     * Repair from a previously failed transaction cut off mid-transaction
     */
    static function repair()
    {
        if (static::$intransaction) {
            throw new AtomicFileTransaction\Exception('Cannot repair while in a transaction');
        }
        static::$inrepair = true;
        static::$allTransactObjects = array();
        $config = Config::current();
        $remove = array();
        foreach ($config->systemvars as $var) {
            if (!strpos($var, '_dir')) {
                continue;
            }

            $path = $config->$var;
            $backuppath = dirname($path) . DIRECTORY_SEPARATOR . '.old-' . basename($path);
            if (file_exists($backuppath) && is_dir($backuppath)) {
                if (file_exists($path)) {
                    if (is_dir($path)) {
                        // this is the new stuff from journal path, so move it out of the way
                        $journalpath = dirname($path) . DIRECTORY_SEPARATOR . '.journal-' . basename($path);
                        $remove[] = $journalpath;
                        rename($path, $journalpath);
                    } else {
                        static::$inrepair = false;
                        throw new AtomicFileTransaction\Exception(
                            'Repair failed - ' . $var . ' path ' . $path .
                            ' is not a directory.  Move this file out of the way and ' .
                            'try the repair again'
                        );
                    }
                }
                // restore backup
                rename($backuppath, $path);
            }
        }
        foreach ($remove as $path) {
            static::rmrf($path);
        }
        static::$inrepair = false;
    }

    /**
     * @param string|Installer\Role\Common $rolepath
     * @return AtomicFileTransaction
     */
    static function getTransactionObject($rolepath)
    {
        if ($rolepath instanceof Installer\Role\Common) {
            $rolepath = Config::current()->{$rolepath->getLocationConfig()};
        }
        if (isset(static::$allTransactObjects[$rolepath])) {
            return static::$allTransactObjects[$rolepath];
        }
        $ret = static::$allTransactObjects[$rolepath] = new AtomicFileTransaction($rolepath);

        if (static::$intransaction) {
            // start the transaction process for this atomic transaction object
            $errs =  new \pear2\MultiErrors;
            try {
                $ret->beginTransaction();
            } catch (\Exception $e) {
                $errs->E_ERROR[] = $e;
                foreach (static::$allTransactObjects as $path2 => $transact) {
                    try {
                        $transact->removeJournalPath();
                    } catch (\Exception $e2) {
                        $errs->E_WARNING[] = $e2;
                    }
                }
                throw new AtomicFileTransaction\Exception(
                    'Unable to begin transaction for ' . $rolepath, $errs
                );
            }
        }
        return $ret;
    }

    function removePath($relativepath, $strict = true)
    {
        if (!static::$intransaction) {
            throw new AtomicFileTransaction\Exception('Cannot remove ' . $relativepath .
                                                                  ' - not in a transaction');
        }
        $path = $this->journalpath . DIRECTORY_SEPARATOR . $relativepath;
        if (!file_exists($path)) {
            return;
        }
        // ensure permissions don't prevent removal
        chmod($path, 0777);
        if (is_dir($path)) {
            if (!@rmdir($path) && $strict) {
                throw new AtomicFileTransaction\Exception(
                    'Cannot remove directory ' . $relativepath . ' in ' . $this->journalpath);
            }
        } else {
            if (!@unlink($path) && $strict) {
                throw new AtomicFileTransaction\Exception(
                    'Cannot remove file ' . $relativepath . ' in ' . $this->journalpath);
            }
        }
    }

    function mkdir($relativepath, $mode = null)
    {
        if (!static::$intransaction) {
            throw new AtomicFileTransaction\Exception('Cannot create directory ' . $relativepath .
                                                                  ' - not in a transaction');
        }
        $path = $this->journalpath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativepath);
        if (file_exists($path)) {
            if (is_dir($path)) {
                return;
            }
            throw new AtomicFileTransaction\Exception('Cannot create directory ' . $relativepath .
                                                                  ', it is a file');
        }
        if (!@mkdir($path, $mode, true)) {
            throw new AtomicFileTransaction\Exception('Unable to make directory ' .
                $relativepath . ' in ' . $this->journalpath);
        }
        if ($mode === null) {
            $mode = $this->defaultMode;
        } else {
            $mode &= 0777;
        }
        if ($mode) {
            chmod($path, $mode);
        }
    }

    /**
     * To perform modifications on a path within the journal transaction
     */
    function openPath($relativepath)
    {
        if (!static::$intransaction) {
            throw new AtomicFileTransaction\Exception('Cannot open ' . $relativepath .
                                                                  ' - not in a transaction');
        }
        $path = $this->journalpath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativepath);
        $fp = @fopen($path, 'rb+');
        if (!$fp) {
            throw new AtomicFileTransaction\Exception('Unable to open ' .
                $relativepath . ' for writing in ' . $this->journalpath);
        }
        return $fp;
    }

    function createOrOpenPath($relativepath, $contents = null, $mode = null)
    {
        if (!static::$intransaction) {
            throw new AtomicFileTransaction\Exception('Cannot create ' . $relativepath .
                                                                  ' - not in a transaction');
        }
        if ($mode === null) {
            $mode = $this->defaultMode;
        } else {
            $mode &= 0777;
        }
        $path = $this->journalpath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativepath);
        if ($contents) {
            if (is_resource($contents)) {
                $fp = @fopen($path, 'wb');
                if (!$fp) {
                    throw new AtomicFileTransaction\Exception('Unable to open ' .
                        $relativepath . ' for writing in ' . $this->journalpath);
                }
                if (false === stream_copy_to_stream($contents, $fp)) {
                    fclose($fp);
                    throw new AtomicFileTransaction\Exception('Unable to copy to ' .
                        $relativepath . ' in ' . $this->journalpath);
                }
                fclose($fp);
            } else {
                if (!@file_put_contents($path, $contents)) {
                    throw new AtomicFileTransaction\Exception('Unable to write to ' .
                        $relativepath . ' in ' . $this->journalpath);
                }
            }
            if ($mode) {
                chmod($path, $mode);
            }
            return $path;
        } else {
            $fp = @fopen($path, 'wb');
            if (!$fp) {
                throw new AtomicFileTransaction\Exception('Unable to open ' .
                    $relativepath . ' for writing in ' . $this->journalpath);
            }
            if ($mode) {
                chmod($path, $mode);
            }
            return $fp;
        }
    }

    /**
     * Remove all empty directories on uninstall
     */
    static function rmEmptyDirs($dirtrees)
    {
        foreach ($dirtrees as $dirtree) {
            foreach ($dirtree as $dir) {
                foreach (static::$allTransactObjects as $path => $obj) {
                    if (0 === strpos($path, $dir)) {
                        // only remove empty directories, don't throw exception on
                        // non-empty directories
                        $obj->rmrf($dir, true, false);
                    }
                }
            }
        }
    }

    static function rmrf($path, $onlyEmptyDirs = false, $strict = true)
    {
        if ($strict) {
            $oldperms = array();
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path),
                                                   \RecursiveIteratorIterator::SELF_FIRST)
                     as $file) {
                if ($file->getFilename() == '.' || $file->getFilename() == '..') {
                    continue;
                }
                $oldperms[$file->getPathName()] = fileperms($file->getPathName());
                if (is_dir($file->getPathName())) {
                    chmod($file->getPathName(), 0777);
                } else {
                    chmod($file->getPathName(), 0666);
                }
            }
        } else {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path),
                                                   \RecursiveIteratorIterator::SELF_FIRST)
                     as $file) {
                if ($file->getFilename() == '.' || $file->getFilename() == '..') {
                    continue;
                }
                if (is_dir($file->getPathName())) {
                    chmod($file->getPathName(), 0777);
                } else {
                    chmod($file->getPathName(), 0666);
                }
            }
        }
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path),
                                               \RecursiveIteratorIterator::CHILD_FIRST)
                 as $file) {
            if ($file->getFilename() == '.' || $file->getFilename() == '..') {
                continue;
            }
            if (is_dir($file->getPathname())) {
                if (!rmdir($file->getPathname())) {
                    if (!$strict) {
                        return;
                    }
                    // restore original permissions
                    foreach ($oldperms as $file => $perms) {
                        if (file_exists($file)) {
                            chmod($file, $perms);
                        }
                    }
                    throw new AtomicFileTransaction\Exception(
                        'Unable to fully remove ' . $path);
                }
            } else {
                if ($onlyEmptyDirs) {
                    if (!$strict) {
                        return;
                    }
                    // restore original permissions
                    foreach ($oldperms as $file => $perms) {
                        if (file_exists($file)) {
                            chmod($file, $perms);
                        }
                    }
                    throw new AtomicFileTransaction\Exception(
                        'Unable to fully remove ' . $path . ', directory is not empty');
                    return;
                }
                if (!unlink($file->getPathname())) {
                    throw new AtomicFileTransaction\Exception(
                        'Unable to fully remove ' . $path);
                }
            }
        }
        // ensure rmdir works
        chmod($path, 0777);
        rmdir($path);
    }

    function copyToJournal()
    {
        if (!file_exists($this->rolepath)) {
            return;
        }
        try {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->rolepath),
                                                   \RecursiveIteratorIterator::SELF_FIRST)
                     as $file) {
                if ($file->getFilename() == '.' || $file->getFilename() == '..') {
                    continue;
                }
                $time = $file->getMTime();
                $atime = $file->getATime();
                $perms = $file->getPerms();
                $src = str_replace($this->rolepath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                if (is_dir($file->getPathname())) {
                    if (!mkdir($this->journalpath . DIRECTORY_SEPARATOR . $src, $perms)) {
                        throw new AtomicFileTransaction\Exception(
                            'Unable to complete journal creation for transaction');
                    }
                    chmod($this->journalpath . DIRECTORY_SEPARATOR . $src, $perms);
                    if (!touch($this->journalpath . DIRECTORY_SEPARATOR . $src, $time, $atime)) {
                        throw new AtomicFileTransaction\Exception(
                            'Unable to complete journal creation for transaction');
                    }
                    continue;
                }
                if (!copy($file->getPathName(), $this->journalpath . DIRECTORY_SEPARATOR . $src)) {
                    throw new AtomicFileTransaction\Exception(
                        'Unable to complete journal creation for transaction');
                }
                if (!touch($this->journalpath . DIRECTORY_SEPARATOR . $src, $time, $atime)) {
                    throw new AtomicFileTransaction\Exception(
                        'Unable to complete journal creation for transaction');
                }
                if (!chmod($this->journalpath . DIRECTORY_SEPARATOR . $src, $perms)) {
                    throw new AtomicFileTransaction\Exception(
                        'Unable to complete journal creation for transaction');
                }
            }
        } catch (\UnexpectedValueException $e) {
            throw new AtomicFileTransaction\Exception('journal creation failed: ' .
                                                      $e->getMessage(), $e);
        }
    }

    static function inTransaction()
    {
        return static::$intransaction;
    }

    static function begin()
    {
        if (static::$intransaction) {
            throw new AtomicFileTransaction\Exception('Cannot begin - already in a transaction');
        }
        $errs = new \pear2\MultiErrors;
        try {
            foreach (static::$allTransactObjects as $path => $transact) {
                $transact->beginTransaction();
            }
            static::$intransaction = true;
        } catch (\Exception $e) {
            static::$intransaction = true;
            $errs->E_ERROR = $e;
            $exit = false;
            foreach (static::$allTransactObjects as $path2 => $transact) {
                if ($exit) {
                    break;
                }
                if ($path2 === $path) {
                    $exit = true;
                }
                try {
                    $transact->removeJournalPath();
                } catch (\Exception $e2) {
                    $errs->E_WARNING[] = $e2;
                }
            }
        }
        if (count($errs)) {
            static::$intransaction = false;
            throw new AtomicFileTransaction\Exception(
                'Unable to begin transaction', $errs
            );
        }
    }

    function beginTransaction()
    {
        $this->committed = false;
        if (!file_exists($this->journalpath)) {
create_journal:
            @mkdir($this->journalpath, 0755, true);
            if (!file_exists($this->journalpath)) {
                throw new AtomicFileTransaction\Exception(
                    'unrecoverable transaction error: cannot create journal path ' . $this->journalpath);
            }
            if (file_exists($this->rolepath)) {
                chmod($this->journalpath, fileperms($this->rolepath));
            } else {
                chmod($this->journalpath, $this->defaultMode);
            }
            $this->copyToJournal();
        } elseif (!is_dir($this->journalpath)) {
            throw new AtomicFileTransaction\Exception(
                'unrecoverable transaction error: journal path ' . $this->journalpath .
                ' exists and is not a directory');
        } else {
            $this->rmrf($this->journalpath);
            goto create_journal;
        }
    }

    static function rollback()
    {
        if (!static::$intransaction) {
            throw new AtomicFileTransaction\Exception('Cannot rollback - not in a transaction');
        }
        foreach (static::$allTransactObjects as $transaction) {
            // restore the original source as quickly as possible
            $transaction->restoreBackup();
        }
        $failed = array();
        $errs = new \pear2\MultiErrors;
        foreach (static::$allTransactObjects as $path => $transaction) {
            try {
                // ... and then delete the transaction
                $transaction->removeJournalPath();
            } catch (AtomicFileTransaction\Exception $e) {
                $errs->E_WARNING[] = $e;
            }
        }
        static::$intransaction = false;
        if (count($errs)) {
            throw new AtomicFileTransaction\Exception('Warning: rollback did not succeed for all transactions',
                                                                  $errs);
        }
    }

    function restoreBackup()
    {
        if (!$this->committed) {
            return;
        }
        if (!file_exists($this->rolepath)) {
            rename($this->backuppath, $this->rolepath);
        } elseif (!file_exists($this->journalpath) && file_exists($this->rolepath)  && file_exists($this->backuppath)) {
            rename($this->rolepath, $this->journalpath);
            rename($this->backuppath, $this->rolepath);
        }
    }

    function removeJournalPath()
    {
        if (!static::$intransaction) {
            throw new AtomicFileTransaction\Exception('Cannot remove journal path - not in a transaction');
        }
        if (!file_exists($this->journalpath) || !is_dir($this->journalpath)) {
            return;
        }
        $this->rmrf($this->journalpath);
    }

    static function commit()
    {
        if (!static::$intransaction) {
            throw new AtomicFileTransaction\Exception('Cannot commit - not in a transaction');
        }
        $errs = new \pear2\MultiErrors;
        try {
            foreach (static::$allTransactObjects as $transaction) {
                $transaction->backupAndCommit();
            }
        } catch (\Exception $e) {
            $errs->E_ERROR[] = $e;
            static::rollback();
        }
        if (count($errs->E_ERROR)) {
            throw new AtomicFileTransaction\Exception('ERROR: commit failed',
                                                                  $errs);
        } elseif (count($errs->E_WARNING)) {
            throw new AtomicFileTransaction\Exception('Warning: removal of backups did not succeed',
                                                                  $errs);
        }
    }

    static function removeBackups()
    {
        if (!static::$intransaction) {
            throw new AtomicFileTransaction\Exception('Cannot remove backups - not in a transaction');
        }
        $errs = new \pear2\MultiErrors;
        foreach (static::$allTransactObjects as $path => $transaction) {
            try {
                $transaction->removeBackup();
            } catch (AtomicFileTransaction\Exception $e) {
                $errs->E_WARNING[] = $e;
            }
        }
        if (count($errs->E_WARNING)) {
            throw new AtomicFileTransaction\Exception('Warning: removal of backups did not succeed',
                                                                  $errs);
        }
        static::$intransaction = false;
    }

    function removeBackup()
    {
        if ($this->committed && file_exists($this->backuppath)) {
            $this->rmrf($this->backuppath);
        }
    }

    function backupAndCommit()
    {
        if (!static::$intransaction) {
            throw new AtomicFileTransaction\Exception('Cannot commit - not in a transaction');
        }
        if ($this->committed) {
            return; // this is here for registry transactions
        }
        if (file_exists($this->backuppath) || (file_exists($this->rolepath)
                                               && !rename($this->rolepath, $this->backuppath))) {
            throw new AtomicFileTransaction\Exception(
                'CRITICAL - unable to complete transaction, rename of actual to backup path failed');
        }
        // here is the only critical moment - a failure in between these two renames
        // leaves us with no source
        if (!rename($this->journalpath, $this->rolepath)) {
            rename($this->backuppath, $this->rolepath);
            throw new AtomicFileTransaction\Exception(
                'CRITICAL - unable to complete transaction, rename of journal to actual path failed');
        }
        $this->committed = true;
    }

    function getJournalPath()
    {
        return $this->journalpath;
    }
}