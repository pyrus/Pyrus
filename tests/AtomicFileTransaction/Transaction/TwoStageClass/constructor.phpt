--TEST--
\PEAR2\Pyrus\AtomicFileTransaction\Transaction\Base::__construct()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$test->assertEquals($journalPath, $instance->getJournalPath(), 'Journal directory');
$test->assertEquals($backupPath, $instance->getBackupPath(), 'Backup directory');
$test->assertFalse($instance->inTransaction(), 'In transaction');
$test->assertFileNotExists($instance->getJournalPath(), 'Journal directory may not exist');
$test->assertFileNotExists($instance->getBackupPath(), 'Backup directory may not exist');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===