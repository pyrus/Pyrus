--TEST--
\Pyrus\AtomicFileTransaction::createOrOpenPath(), path can't be opened, contents is string
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

mkdir($journalPath . '/foo/bar', 0777, true);
try {
    $instance->createOrOpenPath('foo', 'hi', 'wb');
    throw new Exception('Expected exception.');
} catch (\RuntimeException $e) {
    $test->assertEquals('Unable to write to foo in ' . TESTDIR . DIRECTORY_SEPARATOR .
                        '.journal-dir', $e->getMessage(), 'error msg');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===