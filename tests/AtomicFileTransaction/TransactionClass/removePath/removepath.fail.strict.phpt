--TEST--
\Pyrus\AtomicFileTransaction::removePath() failure, strict
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

mkdir($journalPath . '/foo/bar', 0777, true);
$test->assertFileExists($journalPath . '/foo', 'before');
try {
    $instance->removePath('foo');
    throw new Exception('Expected exception.');
} catch (\RuntimeException $e) {
    $test->assertEquals('Cannot remove directory foo in ' . TESTDIR . DIRECTORY_SEPARATOR . '.journal-dir', $e->getMessage(), 'error');
}
$test->assertFileExists($journalPath . '/foo', 'should still exist');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===