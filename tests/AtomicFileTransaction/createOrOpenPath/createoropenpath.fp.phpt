--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::createOrOpenPath(), return open file pointer
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject(__DIR__ . '/testit/src');

\PEAR2\Pyrus\AtomicFileTransaction::begin();

$fp = $atomic->createOrOpenPath('foo', false, 0646);
fwrite($fp, 'hi');
fclose($fp);
$test->assertEquals('hi', file_get_contents(__DIR__ . '/testit/.journal-src/foo'), 'foo contents');

// chmod is not fully supported on windows
if (substr(PHP_OS, 0, 3) != 'WIN') {
	$test->assertEquals(decoct(0646), decoct(0777 & fileperms(__DIR__ . '/testit/.journal-src/foo')), 'perms set');
}
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===