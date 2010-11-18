--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::createOrOpenPath(), contents is stream
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

file_put_contents(TESTDIR . '/blah', 'blah');
$fp = fopen(TESTDIR . '/blah', 'rb');
$instance->createOrOpenPath('foo', $fp, 0664);
fclose($fp);
$test->assertEquals('blah', file_get_contents($journalPath . '/foo'), 'blah contents');

// chmod is not fully supported on windows
if (substr(PHP_OS, 0, 3) != 'WIN') {
	$test->assertEquals(decoct(0664), decoct(0777 & fileperms($journalPath . '/foo')), 'perms set');
}

$instance->rollback();
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===