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
 * @author    Greg Beaver <cellog@php.net>, Warnar Boekkooi
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Atomic file installation infrastructure, guarantees safe installation.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>, Warnar Boekkooi
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus;
use \PEAR2\MultiErrors,
    \PEAR2\Pyrus\Filesystem as FS;

final class AtomicFileTransaction {
    /**
     * @var TransactionManager
     */
    private static $instance = null;

    /**
     * @static
     * @return AtomicFileTransaction\Manager
     */
    public static function singleton()
    {
        if (static::$instance === null) {
            static::$instance = new AtomicFileTransaction\Manager();
        }

        return static::$instance;
    }

    public static function getTransactionObject($rolepath) {
        return static::singleton()->getTransaction($rolepath);
    }

    public static function inTransaction()
    {
        return static::singleton()->inTransaction();
    }

    public static function begin() {
        return static::singleton()->begin();
    }

    public static function rollback() {
        return static::singleton()->rollback();
    }

    public static function commit() {
        return static::singleton()->commit();
    }

    public static function removeBackups() {
        return static::singleton()->finish();
    }

    /**
     * Remove all empty directories on uninstall
     */
    static function rmEmptyDirs($dirTrees)
    {
        $transactionPaths = static::singleton()->getTransactionPaths();
        foreach ($dirTrees as $dirTree) {
            foreach ($dirTree as $dir) {
                $dir = FS::path($dir);
                foreach ($transactionPaths as $path) {
                    if (0 === strpos($path, $dir)) {
                        // only remove empty directories, don't throw exception on
                        // nonempty directories
                        FS::rmrf($dir, true, false);
                    }
                }
            }
        }
    }

    /**
     * Repair from a previously failed transaction cut off mid-transaction
      */
    public static function repair()
    {
        if (static::inTransaction()) {
            throw new AtomicFileTransaction\Exception('Cannot repair while in a transaction');
        }

        static::$instance = null;
        $config = Config::current();
        $remove = array();
        foreach ($config->systemvars as $var) {
            if (!strpos($var, '_dir')) {
                continue;
            }

            $path = FS::path($config->$var);
            $backuppath = dirname($path) . DIRECTORY_SEPARATOR . '.old-' . basename($path);
            if (file_exists($backuppath) && is_dir($backuppath)) {
                if (file_exists($path)) {
                    if (!is_dir($path)) {
                        throw new AtomicFileTransaction\Exception(
                            'Repair failed - ' . $var . ' path ' . $path .
                            ' is not a directory.  Move this file out of the way and ' .
                            'try the repair again'
                        );
                    }

                    // this is the new stuff from journal path, so move it out of the way
                    $journalpath = dirname($path) . DIRECTORY_SEPARATOR . '.journal-' . basename($path);
                    $remove[] = $journalpath;
                    rename($path, $journalpath);
                }

                // restore backup
                rename($backuppath, $path);
            }
        }

        foreach ($remove as $path) {
            FS::rmrf($path);
        }
    }
}
