<?php
namespace PEAR2\Pyrus\AtomicFileTransaction;

use PEAR2\MultiErrors,
    PEAR2\Pyrus\Filesystem as FS;

class Manager {
    /**
     * @var Transaction[]
     */
    protected $transactions = array();

    /**
     * @var boolean
     */
    protected $intransaction;

    /**
     * The transaction class to instantiate.
     * This is useful for unit-testing and for extensions.
     * @var string
     */
    protected $className = 'PEAR2\Pyrus\AtomicFileTransaction\Transaction';

    public function inTransaction() {
        return $this->intransaction;
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
            throw new Exception('Unable to begin transaction', $errs);
        }
    }

    public function getTransactionPaths() {
        return array_keys($this->transactions);
    }

    public function getTransactionClass() {
        return $this->className;
    }

    public function setTransactionClass($className) {
        if (!@class_exists($className)) {
            throw new \InvalidArgumentException('className must be a valid class - class cannot be loaded.');
        }
        $this->className = $className;
        return $this;
    }

    public function begin()
    {
        if ($this->inTransaction()) {
            throw new Exception('Cannot begin - already in a transaction');
        }

        $this->intransaction = true;
        foreach ($this->transactions as $transaction) {
            $this->beginTransaction($transaction);
        }
    }

    public function rollback()
    {
        if (!$this->inTransaction()) {
            throw new Exception('Cannot rollback - not in a transaction');
        }

        $errs = $this->rollbackTransactions();

        $this->intransaction = false;
        if (count($errs->E_ERROR)) {
            throw new Exception('ERROR: rollback failed', $errs);
        } elseif (count($errs->E_WARNING)) {
            throw new Exception('Warning: rollback did not succeed for all transactions', $errs);
        }
    }

    public function commit()
    {
        if (!$this->inTransaction()) {
            throw new Exception('Cannot commit - not in a transaction');
        }

        try {
            foreach ($this->transactions as $transaction) {
                $transaction->commit();
            }
        } catch (\Exception $e) {
            $errs = new \PEAR2\MultiErrors();
            $errs->E_ERROR[] = $e;
            $errs->merge($this->rollbackTransactions());
            throw new Exception('ERROR: commit failed', $errs);
        }
    }

    public function finish() {
        if (!$this->inTransaction()) {
            throw new Exception('Cannot finish - not in a transaction');
        }

        foreach ($this->transactions as $transaction) {
            if ($transaction->inTransaction()) {
                throw new Exception('Cannot remove backups - not all transactions have been committed');
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
        $this->intransaction = false;

        if (count($errs->E_WARNING)) {
            throw new Exception('Warning: no all backup directories have been removed', $errs);
        }
    }

    protected function beginTransaction(Transaction $transaction) {
        try {
            if (!$transaction->inTransaction()) {
                $transaction->begin();
            }
        } catch (\Exception $e) {
            $errs = new MultiErrors();
            $errs->E_ERROR[] = $e;
            $errs->merge($this->rollbackTransactions());
            throw new Exception('Unable to begin transaction', $errs);
        }
    }

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

        $this->intransaction = false;
        return $rtn;
    }
}