--TEST--
\Pyrus\AtomicFileTransaction::createOrOpenPath(), path can't be opened
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

\Pyrus\AtomicFileTransaction::begin();

mkdir(TESTDIR . '/.journal-src/foo/bar', 0777, true);
try {
    $atomic->createOrOpenPath('foo', null, 'wb');
    throw new Exception('Expected exception.');
} catch (RuntimeException $e) {
    $test->assertEquals('Unable to open foo for writing in ' . TESTDIR . DIRECTORY_SEPARATOR .
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