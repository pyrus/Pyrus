--TEST--
\Pyrus\AtomicFileTransaction::mkdir(), use default mode
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';
getTestConfig();

$old = umask(0444); // confirm this does not affect things
\Pyrus\Config::current()->umask = 0002;
$atomic = \Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');
\Pyrus\AtomicFileTransaction::begin();

$atomic->mkdir('good');

\Pyrus\AtomicFileTransaction::commit();

$test->assertFileExists(TESTDIR . '/src/good', TESTDIR . '/src/good should exist');

// chmod is not fully supported on windows
if (substr(PHP_OS, 0, 3) != 'WIN') {
	$test->assertEquals(decoct(0775), decoct(0777 & fileperms(TESTDIR . '/src/good')), 'permissions should work');
	$test->assertEquals(decoct(0775), decoct(0777 & fileperms(TESTDIR . '/src')), 'permissions should work src');
}

umask($old);
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===