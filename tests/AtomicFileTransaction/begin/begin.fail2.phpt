--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::begin(), journal dir exists as file 2
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

touch(TESTDIR . '/.journal-src');


\PEAR2\Pyrus\AtomicFileTransaction::begin();

try {
    $atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');
    throw new Exception('Expected exception.');
} catch (\PEAR2\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Unable to begin transaction for ' . TESTDIR . DIRECTORY_SEPARATOR . 'src', $e->getMessage(), 'main message');
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
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===