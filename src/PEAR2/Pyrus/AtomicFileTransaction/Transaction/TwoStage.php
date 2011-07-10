<?php
namespace Pyrus\AtomicFileTransaction\Transaction;

use Pyrus\IOException,
    Pyrus\AtomicFileTransaction\RuntimeException,
    Pyrus\Filesystem as FS;
 /**
 * A two stage transaction class that uses a journal to save changes, it also creates a backup on commit.
 * Using the finish function the backup will be removed,
 * on revert the backup will be place back and the journal will be destroyed.
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Warnar Boekkooi
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
class TwoStage extends Base
{
    /**
     * @var string
     */
    protected $backupPath;

    /**
     * Constructor.
     * @param string $path The path for which a transaction is required.
     */
    public function __construct($path)
    {
        parent::__construct($path);
        $this->backupPath = FS::combine(dirname($this->path), '.old-' . basename($path));
    }

    /**
     * Begin the file system transaction.
     * Create the journal path and copy all files to it from the original path.
     *
     * @throws Pyrus\AtomicFileTransaction\RuntimeException Thrown when a old backup directory is found.
     * @return void
     */
    public function begin()
    {
        if ($this->hasBackup()) {
            throw new RuntimeException('Cannot begin - a backup directory still exists');
        }

        parent::begin();
    }

    /**
     * Get the backup path of the transaction.
     * @return string
     */
    public function getBackupPath()
    {
        return $this->backupPath;
    }

    /**
     * Indicated if the transaction has a backup directory.
     *
     * @return bool
     */
    public function hasBackup()
    {
        return file_exists($this->backupPath) || is_dir($this->backupPath);
    }

    /**
     * Commit the current transaction and create a backup of old the filesystem.
     * This method finishes the first commit stage of transaction.
     *
     * @return void
     */
    public function commit()
    {
        $this->checkActive();

        if ($this->hasBackup() || (file_exists($this->path)
                                             && !rename($this->path, $this->backupPath))) {
            throw new IOException(
                'CRITICAL - unable to complete transaction, rename of actual to backup path failed');
        }

        try {
            parent::commit();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Finish the second commit stage.
     * This removes the backup directory.
     *
     * @return void
     */
    public function finish()
    {
        if ($this->inTransaction) {
            throw new RuntimeException('Cannot finish - still in a transaction');
        }

        if ($this->hasBackup() && file_exists($this->backupPath)) {
            FS::rmrf($this->backupPath);
        }
    }

    /**
     * Rollback the entire transaction.
     * This should restore the backup and remove any other new files.
     *
     * @return void
     */
    public function revert()
    {
        if ($this->inTransaction) {
            throw new RuntimeException('Cannot revert - still in a transaction');
        }

        if (!$this->hasBackup()) {
            return;
        }

        if (!file_exists($this->backupPath)) {
            throw new IOException('Cannot restore backup, no backup directory available.');
        }
        if (file_exists($this->journalPath)) {
            throw new IOException('Cannot restore backup, a journal directory/file still exists.');
        }

        // Rename current path to journal path
        if (file_exists($this->path)) {
            if (!rename($this->path, $this->journalPath)) {
                throw new IOException('Cannot restore backup, unable to rename directory.');
            }
        }

        // Rename backup path to path
        if (!rename($this->backupPath, $this->path)) {
            throw new IOException('Cannot restore backup, unable to rename backup directory.');
        }

        // Remove journal directory
        if (file_exists($this->journalPath)) {
            FS::rmrf($this->journalPath);
        }
    }
}
