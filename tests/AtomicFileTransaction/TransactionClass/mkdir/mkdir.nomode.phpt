--TEST--
\Pyrus\AtomicFileTransaction::mkdir(), use default mode
--FILE--
<?php
require __DIR__ . '/../../../test_framework.php.inc';
getTestConfig();

$old = umask(0444); // confirm this does not affect things
\Pyrus\Config::current()->umask = 0002;

$path = TESTDIR . DIRECTORY_SEPARATOR . 'dir';
$journalPath = TESTDIR . DIRECTORY_SEPARATOR . '.journal-dir';

$manager = \Pyrus\AtomicFileTransaction::singleton();
$manager->begin();
$instance = $manager->getTransaction($path);
$instance->mkdir('good');

$instance->commit();

$test->assertFileExists($path . '/good', $path . '/good should exist');

// chmod is not fully supported on windows
if (substr(PHP_OS, 0, 3) != 'WIN') {
	$test->assertEquals(decoct(0775), decoct(0777 & fileperms($path . '/good')), 'permissions should work');
	$test->assertEquals(decoct(0775), decoct(0777 & fileperms($path)), 'permissions should work src');
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