<?php
namespace PEAR2\Pyrus\AtomicFileTransaction\Transaction;

use PEAR2\Pyrus\IOException,
    PEAR2\Pyrus\AtomicFileTransaction\RuntimeException,
    PEAR2\Pyrus\Filesystem as FS;

/**
 * A simple transaction class that uses a journal to save changes.
 * This transaction class created a journal directory of the path the transaction is for,
 * once committed it will remove the original path and replace it with the journal path.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Warnar Boekkooi
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
class Base
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $journalPath;

    /**
     * @var bool
     */
    protected $inTransaction = false;

    /**
     * Constructor.
     * @throws InvalidArgumentException Thrown when a invalid path is supplied.
     * @param string $path The path for which a transaction is required.
     */
    public function __construct($path)
    {
        if (empty($path) || !is_string($path) || file_exists($path) && !is_dir($path)) {
            throw new \InvalidArgumentException('The given path must be a directory.');
        }
        $this->path = FS::path($path);

        $parentPath = dirname($path);
        $this->journalPath = FS::combine($parentPath, '.journal-' . basename($path));
    }

    /**
     * Indicated if the transaction is active (has begone).
     *
     * @return bool
     */
    public function inTransaction()
    {
        return $this->inTransaction;
    }

    /**
     * Get the journal path of the transaction.
     *
     * @return string
     */
    public function getJournalPath()
    {
        return $this->journalPath;
    }

    /**
     * Begin the file system transaction.
     * Create the journal path and copy all files to it from the original path.
     *
     * @return void
     */
    public function begin()
    {
        if ($this->inTransaction) {
            throw new RuntimeException('Cannot begin - already in a transaction');
        }

        // Remove possible failed transaction
        if (file_exists($this->journalPath)) {
            if(!is_dir($this->journalPath)) {
                throw new IOException('unrecoverable transaction error: journal path ' . $this->journalPath . ' exists and is not a directory');
            }
            FS::rmrf($this->journalPath);
        }

        // Create the journal directory
        @mkdir($this->journalPath, 0755, true);
        if (!is_dir($this->journalPath)) {
            throw new IOException('unrecoverable transaction error: cannot create journal path ' . $this->journalPath);
        }

        // Set permissions
        chmod($this->journalPath, file_exists($this->path) ? fileperms($this->path) : $this->getMode());

        // Copy source to the journal
        FS::copyDir($this->path, $this->journalPath);

        $this->inTransaction = true;
    }

    /**
     * Rollback the transaction.
     * This will remove the journal path.
     *
     * @return void
     */
    public function rollback()
    {
        $this->checkActive();

        if (file_exists($this->journalPath) && is_dir($this->journalPath)) {
            FS::rmrf($this->journalPath);
        }
        $this->inTransaction = false;
    }

    /**
     * Commit the transaction.
     * Replaces the journal path with the original path.
     *
     * @throws RuntimeException
     * @return void
     */
    public function commit()
    {
        $this->checkActive();

        if (file_exists($this->path)) {
            FS::rmrf($this->path);
        }

        // here is the only critical moment - a failure in between these two renames
        // leaves us with no source
        if (!@rename($this->journalPath, $this->path)) {
            throw new IOException('CRITICAL - unable to complete transaction, rename of journal to actual path failed');
        }

        $this->inTransaction = false;
    }

    /**
     * Check's if the transaction is active.
     *
     * @throws RuntimeException Thrown when the transaction in inactive.
     * @return void
     */
    protected function checkActive()
    {
        if (!$this->inTransaction) {
            throw new RuntimeException('Transaction not active.');
        }
    }

    protected function getMode($mode = null) {
        if ($mode === null) {
            return 0777 & ~octdec(\PEAR2\Pyrus\Config::current()->umask);
        }
        return $mode & 0777;
    }
}
