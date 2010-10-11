--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::createOrOpenPath(), failure, contents is empty stream
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

\PEAR2\Pyrus\AtomicFileTransaction::begin();

file_put_contents(TESTDIR . '/blah', 'blah');
$fp = fopen(TESTDIR . '/blah', 'rb');
fread($fp, 55);
try {
    $atomic->createOrOpenPath('foo', $fp, 0664);
    fclose($fp);
    throw new Exception('Expected exception.');
} catch (\PEAR2\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Unable to copy to foo in ' . TESTDIR . DIRECTORY_SEPARATOR . '.journal-src', $e->getMessage(), 'error message');
}
fclose($fp);
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===