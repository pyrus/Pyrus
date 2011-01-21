--TEST--

--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

$instance->begin();
$instance->getTransaction(TESTDIR . '/foo');

$test->assertFileExists(TESTDIR . '/.journal-foo', 'before commit');
$test->assertFileNotExists(TESTDIR . '/foo', 'before commit');

$instance->commit();

$test->assertFileNotExists(TESTDIR . '/.journal-foo', 'after commit');
$test->assertFileExists(TESTDIR . '/foo', 'after commit');
$test->assertFileNotExists(TESTDIR . '/.old-foo', 'after commit');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===