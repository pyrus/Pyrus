<?php
require __DIR__ . '/../../../test_framework.php.inc';

$path = TESTDIR . DIRECTORY_SEPARATOR . 'dir';
$journalPath = TESTDIR . DIRECTORY_SEPARATOR . '.journal-dir';
$backupPath = TESTDIR . DIRECTORY_SEPARATOR . '.old-dir';

$instance = new \PEAR2\Pyrus\AtomicFileTransaction\Transaction\TwoStage($path);