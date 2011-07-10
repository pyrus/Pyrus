<?php
require __DIR__ . '/../../../test_framework.php.inc';

$path = TESTDIR . DIRECTORY_SEPARATOR . 'dir';
$journalPath = TESTDIR . DIRECTORY_SEPARATOR . '.journal-dir';

class BaseMock extends \Pyrus\AtomicFileTransaction\Transaction\Base {
    public static function rmrf($path, $onlyEmptyDirs = false, $strict = true) {
        parent::rmrf($path, $onlyEmptyDirs, $strict);
    }
}

$instance = new \Pyrus\AtomicFileTransaction\Transaction\Base($path);