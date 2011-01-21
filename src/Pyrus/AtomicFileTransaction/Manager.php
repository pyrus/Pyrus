<?php
namespace PEAR2\Pyrus\AtomicFileTransaction;

use PEAR2\MultiErrors,
    PEAR2\Pyrus\Filesystem as FS;

/**
 * A Atomic file transaction manager class.
 * This class helps manage multiple transactions
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Warnar Boekkooi, Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
class Manager {
    /**
     * @var Transaction[]
     */
    protected $transactions = array();

    /**
     * @var boolean
     */
    protected $inTransaction;

    /**
     * The transaction class to instantiate.
     * This is useful for unit-testing and for extensions.
     * @var string
     */
    protected $className = 'PEAR2\Pyrus\AtomicFileTransaction\Transaction';

    /**
     * Indicated if the manager has active transactions.
     *
     * @return bool
     */
    public function inTransaction() {
        return $this->inTransaction;
    }

    /**
     * Get/Create a transaction.
     *
     * @param string|PEAR2\Pyrus\Installer\Role\Common $path The directory path.
     * @return Transaction A PEAR2\Pyrus\AtomicFileTransaction\Transaction instance
     */
    public function getTransaction($path) {
        if ($path instanceof \PEAR2\Pyrus\Installer\Role\Common) {
            $path = \PEAR2\Pyrus\Config::current()->{$path->getLocationConfig()};
        }
        $path = FS::path((string)$path);

        if (isset($this->transactions[$path])) {
            return $this->transactions[$path];
        }

        // Create new transaction object
        $class = $this->getTransactionClass();
        try {
            $this->transactions[$path] = new $class($path, $this);
            return $this->transactions[$path];
        } catch (\Exception $e) {
            $errs = new MultiErrors();
            $errs->E_ERROR[] = $e;
            $errs->merge($this->rollbackTransactions());
            throw new MultiException('Unable to begin transaction', $errs);
        }
    }

    /**
     * Get a list of all transaction path's.
     *
     * @return array
     */
    public function getTransactionPaths() {
        return array_keys($this->transactions);
    }

    /**
     * Get the name of the transaction class that is being used.
     *
     * @return string
     */
    public function getTransactionClass() {
        return $this->className;
    }

    /**
     * Set the transaction class name.
     *
     * @param string $className
     * @return Manager
     */
    public function setTransactionClass($className) {
        if (!@class_exists($className)) {
            throw new \InvalidArgumentException('className must be a valid class - class cannot be loaded.');
        }
        $this->className = (string)$className;
        return $this;
    }

    /**
     * Begin all transactions in the manager.
     *
     * @throws RuntimeException
     * @return void
     */
    public function begin()
    {
        if ($this->inTransaction()) {
            throw new RuntimeException('Cannot begin - already in a transaction');
        }

        $this->inTransaction = true;
        foreach ($this->transactions as $transaction) {
            $this->beginTransaction($transaction);
        }
    }

    /**
     * Rollback all transaction changes.
     *
     * @throws RuntimeException
     * @return void
     */
    public function rollback()
    {
        if (!$this->inTransaction()) {
            throw new RuntimeException('Cannot rollback - not in a transaction');
        }

        $errs = $this->rollbackTransactions();

        $this->inTransaction = false;
        if (count($errs->E_ERROR)) {
            throw new MultiException('ERROR: rollback failed', $errs);
        } elseif (count($errs->E_WARNING)) {
            throw new MultiException('Warning: rollback did not succeed for all transactions', $errs);
        }
    }

    /**
     * Commit all transactions.
     *
     * @return void
     */
    public function commit()
    {
        if (!$this->inTransaction()) {
            throw new RuntimeException('Cannot commit - not in a transaction');
        }

        try {
            foreach ($this->transactions as $transaction) {
                $transaction->commit();
            }
        } catch (\Exception $e) {
            $errs = new \PEAR2\MultiErrors();
            $errs->E_ERROR[] = $e;
            $errs->merge($this->rollbackTransactions());
            throw new MultiException('ERROR: commit failed', $errs);
        }
    }

    /**
     * Finish all transactions.
     * This will remove any old journal and backup directories.
     *
     * @return void
     */
    public function finish() {
        if (!$this->inTransaction()) {
            throw new RuntimeException('Cannot finish - not in a transaction');
        }

        foreach ($this->transactions as $transaction) {
            if ($transaction->inTransaction()) {
                throw new RuntimeException('Cannot remove backups - not all transactions have been committed');
            }
        }

        $errs = new \PEAR2\MultiErrors();
        foreach ($this->transactions as $transaction) {
            try {
                $transaction->finish();
            } catch (\Exception $e) {
                $errs->E_WARNING[] = $e;
            }
        }
        $this->inTransaction = false;

        if (count($errs->E_WARNING)) {
            throw new MultiException('Warning: no all backup directories have been removed', $errs);
        }
    }

    /**
     * Begin a single transaction.
     *
     * @param Transaction $transaction
     * @return void
     */
    protected function beginTransaction(Transaction $transaction) {
        try {
            if (!$transaction->inTransaction()) {
                $transaction->begin();
            }
        } catch (\Exception $e) {
            $errs = new MultiErrors();
            $errs->E_ERROR[] = $e;
            $errs->merge($this->rollbackTransactions());
            throw new MultiException('Unable to begin transaction', $errs);
        }
    }

    /**
     * Rollback/revert all transactions and return any exceptions thrown.
     *
     * @return PEAR2\MultiErrors
     */
    protected function rollbackTransactions() {
        $rtn = new \PEAR2\MultiErrors;

        // restore the original source as quickly as possible
        foreach ($this->transactions as $transaction) {
            if (!$transaction->hasBackup()) {
                continue;
            }
            try {
                $transaction->revert();
            } catch (\Exception $e) {
                $rtn->E_ERROR[] = $e;
            }
        }

        // Remove the journal's and other uncommitted stuff
        foreach ($this->transactions as $transaction) {
            if (!$transaction->inTransaction()) {
                continue;
            }
            try {
                $transaction->rollback();
            } catch (\Exception $e) {
                $rtn->E_WARNING[] = $e;
            }
        }

        $this->inTransaction = false;
        return $rtn;
    }
}