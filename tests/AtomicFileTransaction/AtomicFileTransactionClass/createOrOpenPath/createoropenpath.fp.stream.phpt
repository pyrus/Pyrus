--TEST--
\Pyrus\AtomicFileTransaction::createOrOpenPath(), contents is stream
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

\Pyrus\AtomicFileTransaction::begin();

file_put_contents(TESTDIR . '/blah', 'blah');
$fp = fopen(TESTDIR . '/blah', 'rb');
$atomic->createOrOpenPath('foo', $fp, 0664);
fclose($fp);
$test->assertEquals('blah', file_get_contents(TESTDIR . '/.journal-src/foo'), 'blah contents');

// chmod is not fully supported on windows
if (substr(PHP_OS, 0, 3) != 'WIN') {
	$test->assertEquals(decoct(0664), decoct(0777 & fileperms(TESTDIR . '/.journal-src/foo')), 'perms set');
}

\Pyrus\AtomicFileTransaction::rollback();
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===