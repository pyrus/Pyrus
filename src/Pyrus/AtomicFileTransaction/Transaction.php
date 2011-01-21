<?php
namespace PEAR2\Pyrus\AtomicFileTransaction;

use PEAR2\Pyrus\IOException;

/**
 * A transaction for use by a Manager instance.
 * This class also includes some file operation helper functions.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Warnar Boekkooi, Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
class Transaction extends Transaction\TwoStage
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * Constructor.
     *
     * @param string $path The path for which a transaction is required.
     * @param Manager $manager The manager that the transaction belongs to.
     */
    public function __construct($path, Manager $manager)
    {
        parent::__construct($path);
        $this->manager = $manager;

        if ($manager->inTransaction()) {
            $this->begin();
        }
    }

    /**
     * Begin the file system transaction.
     * If the manager has not begone the transaction force it to begin.
     *
     * @return void
     */
    public function begin()
    {
        if (!$this->manager->inTransaction()) {
            $this->manager->begin();
            return;
        }
        parent::begin();
    }

    /**
     * Remove a path from the journal directory.
     *
     * @param string $relativePath
     * @param bool $strict When TRUE a exception will when the path can't be removed
     * @return bool TRUE on success else FALSE
     */
    public function removePath($relativePath, $strict = true)
    {
        $this->checkActive();
        $path = $this->getPath($relativePath);

        if (!file_exists($path)) {
            return true;
        }

        // ensure permissions don't prevent removal
        chmod($path, 0777);
        if (is_dir($path)) {
            if (@rmdir($path)) {
                return true;
            }
            if ($strict) {
                throw new IOException('Cannot remove directory ' . $relativePath . ' in ' . $this->journalPath);
            }
        } else {
            if (@unlink($path)) {
                return true;
            }
            if ($strict) {
                throw new IOException('Cannot remove file ' . $relativePath . ' in ' . $this->journalPath);
            }
        }
        return false;
    }

    /**
     * Create a directory in the transaction journal.
     *
     * @param string $relativePath
     * @param null $mode
     * @return void
     */
    public function mkdir($relativePath, $mode = null)
    {
        $this->checkActive();
        $path = $this->getPath($relativePath);

        if (file_exists($path)) {
            if (is_dir($path)) {
                return;
            }
            throw new IOException('Cannot create directory ' . $relativePath . ', it is a file');
        }

        if (!@mkdir($path, $mode, true)) {
            throw new IOException('Unable to make directory ' . $relativePath . ' in ' . $this->journalPath);
        }

        $mode = $this->getMode($mode);
        if ($mode) {
            chmod($path, $mode);
        }
    }

    public function createOrOpenPath($relativePath, $contents = null, $mode = null)
    {
        $this->checkActive();

        $mode = $this->getMode($mode);

        $path = $this->getPath($relativePath);

        $rtn = $path;
        if (is_resource($contents)) {
            $fp = @fopen($path, 'wb');
            if (!$fp) {
                throw new IOException('Unable to open ' . $relativePath . ' for writing in ' . $this->journalPath);
            }

            // Also throw a exception when zero is returned
            if (stream_copy_to_stream($contents, $fp) === false) {
                fclose($fp);
                throw new IOException('Unable to copy to ' . $relativePath . ' in ' . $this->journalPath);
            }

            fclose($fp);
        } elseif ($contents) {
            if (!@file_put_contents($path, $contents)) {
                throw new IOException('Unable to write to ' . $relativePath . ' in ' . $this->journalPath);
            }
        } else {
            $fp = @fopen($path, 'wb');
            if (!$fp) {
                throw new IOException('Unable to open ' . $relativePath . ' for writing in ' . $this->journalPath);
            }
            $rtn = $fp;
        }

        if ($mode) {
            chmod($path, $mode);
        }

        return $rtn;
    }

    /**
     * To perform modifications on a path within the journal transaction.
     *
     * @param string $relativePath
     * @return resource A file pointer resource
     */
    public function openPath($relativePath)
    {
        $this->checkActive();
        $path = $this->getPath($relativePath);

        $fp = @fopen($path, 'rb+');
        if (!$fp) {
            throw new IOException('Unable to open ' . $relativePath . ' for writing in ' . $this->journalPath);
        }
        return $fp;
    }

    /**
     * Check's if the transaction is active.
     *
     * @throws RuntimeException Thrown when the transaction in inactive.
     * @return void
     */
    protected function checkActive()
    {
        if (!$this->inTransaction || !$this->manager->inTransaction()) {
            throw new RuntimeException('Transaction not active.');
        }
    }

    /**
     * Get the full journal path based on a relative path.
     *
     * @param string $relativePath
     * @return string
     */
    protected function getPath($relativePath)
    {
        return \PEAR2\Pyrus\Filesystem::path($this->journalPath . '/' . (string)$relativePath);
    }
}
