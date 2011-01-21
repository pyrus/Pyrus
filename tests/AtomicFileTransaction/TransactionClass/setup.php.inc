<?php
require __DIR__ . '/../../test_framework.php.inc';

$path = TESTDIR . DIRECTORY_SEPARATOR . 'dir';
$journalPath = TESTDIR . DIRECTORY_SEPARATOR . '.journal-dir';
$backupPath = TESTDIR . DIRECTORY_SEPARATOR . '.old-dir';

$manager = \PEAR2\Pyrus\AtomicFileTransaction::singleton();
$manager->begin();
$instance = $manager->getTransaction($path);