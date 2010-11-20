<?php

class TransactionMock extends \PEAR2\Pyrus\AtomicFileTransaction\Transaction
{
    public static $failBegin;
    public static $failRollback;
    public static $failCommit;
    public static $failFinish;
    public static $failRevert;

    public function __construct($path, \PEAR2\Pyrus\AtomicFileTransaction\Manager $manager)
    {
        parent::__construct($path, $manager);
    }

    public function begin() {
        parent::begin();
        if(static::$failBegin) {
            throw new \RuntimeException('beginTransaction failed');
        }
    }

    public function commit() {
        parent::commit();
        if(static::$failCommit) {
            throw new \RuntimeException('commit failed');
        }
    }

    public function rollback() {
        parent::rollback();
        if(static::$failRollback) {
            throw new \RuntimeException('beginTransaction failed');
        }
    }

    public function finish() {
        parent::finish();
        if(static::$failFinish) {
            throw new \RuntimeException('finish failed');
        }
    }


    public function revert() {
        parent::revert();
        if(static::$failRevert) {
            throw new \RuntimeException('revert failed');
        }
    }
}
