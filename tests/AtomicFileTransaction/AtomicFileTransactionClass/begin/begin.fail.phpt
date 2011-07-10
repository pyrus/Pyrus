--TEST--
\Pyrus\AtomicFileTransaction::begin(), journal dir exists as file
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

touch(TESTDIR . '/.journal-src');

$atomic = \Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

try {
    \Pyrus\AtomicFileTransaction::begin();
    throw new Exception('Expected exception.');
} catch (\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Unable to begin transaction', $e->getMessage(), 'main message');
    $causes = array();
    $e->getCauseMessage($causes);
    $test->assertEquals('unrecoverable transaction error: journal path ' .
                        TESTDIR . DIRECTORY_SEPARATOR .
                        '.journal-src exists and is not a directory', $causes[1]['message'], 'error message');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===