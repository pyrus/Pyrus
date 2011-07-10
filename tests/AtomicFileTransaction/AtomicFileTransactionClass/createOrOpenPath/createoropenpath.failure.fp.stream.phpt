--TEST--
\Pyrus\AtomicFileTransaction::createOrOpenPath(), path can't be opened, contents is stream
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

\Pyrus\AtomicFileTransaction::begin();

mkdir(TESTDIR . '/.journal-src/foo/bar', 0777, true);
file_put_contents(TESTDIR . '/blah', 'blah');
$fp = fopen(TESTDIR . '/blah', 'rb');
try {
    $atomic->createOrOpenPath('foo', $fp, 'wb');
    throw new Exception('Expected exception.');
} catch (RuntimeException $e) {
    $test->assertEquals('Unable to open foo for writing in ' . TESTDIR . DIRECTORY_SEPARATOR .
                        '.journal-src', $e->getMessage(), 'error msg');
}
fclose($fp);
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===