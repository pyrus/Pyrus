--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::createOrOpenPath(), path can't be opened, contents is string
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

\PEAR2\Pyrus\AtomicFileTransaction::begin();

mkdir(TESTDIR . '/.journal-src/foo/bar', 0777, true);
try {
    $atomic->createOrOpenPath('foo', 'hi', 'wb');
    throw new Exception('Expected exception.');
} catch (RuntimeException $e) {
    $test->assertEquals('Unable to write to foo in ' . TESTDIR . DIRECTORY_SEPARATOR .
                        '.journal-src', $e->getMessage(), 'error msg');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===