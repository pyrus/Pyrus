<?php
namespace PEAR2\Pyrus\AtomicFileTransaction;

class Transaction extends Transaction\TwoStage {
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * Constructor.
     * @param string $path The path for which a transaction is required.
     */
    public function __construct($path, Manager $manager) {
        parent::__construct($path);
        $this->manager = $manager;

        if ($manager->inTransaction()) {
            $this->begin();
        }
    }

    public function begin()
    {
        if (!$this->manager->inTransaction()) {
            $this->manager->begin();
            return;
        }
        parent::begin();
    }

    public function removePath($relativePath, $strict = true)
    {
        $this->checkActive();
        $path = $this->getPath($relativePath);

        if (!file_exists($path)) {
            return;
        }

        // ensure permissions don't prevent removal
        chmod($path, 0777);
        if (is_dir($path)) {
            if (!@rmdir($path) && $strict) {
                throw new \RuntimeException('Cannot remove directory ' . $relativePath . ' in ' . $this->journalPath);
            }
        } elseif (!@unlink($path) && $strict) {
            throw new \RuntimeException('Cannot remove file ' . $relativePath . ' in ' . $this->journalPath);
        }
    }

    public function mkdir($relativePath, $mode = null)
    {
        $this->checkActive();
        $path = $this->getPath($relativePath);

        if (file_exists($path)) {
            if (is_dir($path)) {
                return;
            }
            throw new \RuntimeException('Cannot create directory ' . $relativePath . ', it is a file');
        }

        if ($mode === null) {
            $mode = $this->defaultMode;
        } else {
            $mode &= 0777;
        }

        if (!@mkdir($path, $mode, true)) {
            throw new \RuntimeException('Unable to make directory ' . $relativePath . ' in ' . $this->journalPath);
        }
    }

    public function createOrOpenPath($relativePath, $contents = null, $mode = null)
    {
        $this->checkActive();

        if ($mode === null) {
            $mode = $this->defaultMode;
        } else {
            $mode &= 0777;
        }

        $path = $this->getPath($relativePath);

        $rtn = $path;
        if (is_resource($contents)) {
            $fp = @fopen($path, 'wb');
            if (!$fp) {
                throw new \RuntimeException('Unable to open ' . $relativePath . ' for writing in ' . $this->journalPath);
            }

            if (false === stream_copy_to_stream($contents, $fp)) {
                fclose($fp);
                throw new \RuntimeException('Unable to copy to ' . $relativePath . ' in ' . $this->journalPath);
            }

            fclose($fp);
        } elseif ($contents) {
            if (!@file_put_contents($path, $contents)) {
                throw new \RuntimeException('Unable to write to ' . $relativePath . ' in ' . $this->journalPath);
            }
        } else {
            $fp = @fopen($path, 'wb');
            if (!$fp) {
                throw new \RuntimeException('Unable to open ' . $relativePath . ' for writing in ' . $this->journalPath);
            }
            $rtn = $fp;
        }

        if ($mode) {
            chmod($path, $mode);
        }

        return $rtn;
    }

    public function openPath($relativePath)
    {
        $this->checkActive();
        $path = $this->getPath($relativePath);

        $fp = @fopen($path, 'rb+');
        if (!$fp) {
            throw new \RuntimeException('Unable to open ' . $relativePath . ' for writing in ' . $this->journalPath);
        }
        return $fp;
    }

    protected function checkActive() {
        if (!$this->inTransaction || !$this->manager->inTransaction()) {
            throw new \RuntimeException('Transaction not active.');
        }
    }

    protected function getPath($relativePath) {
        return \PEAR2\Pyrus\Filesystem::path($this->journalPath . '/' . $relativePath);
    }
}
