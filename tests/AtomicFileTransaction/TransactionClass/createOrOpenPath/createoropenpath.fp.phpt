--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::createOrOpenPath(), return open file pointer
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

$fp = $instance->createOrOpenPath('foo', false, 0646);
fwrite($fp, 'hi');
fclose($fp);
$test->assertEquals('hi', file_get_contents($journalPath . '/foo'), 'foo contents');

// chmod is not fully supported on windows
if (substr(PHP_OS, 0, 3) != 'WIN') {
	$test->assertEquals(decoct(0646), decoct(0777 & fileperms(TESTDIR . '/.journal-src/foo')), 'perms set');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===