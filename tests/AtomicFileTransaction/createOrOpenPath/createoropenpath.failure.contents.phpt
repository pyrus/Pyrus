--TEST--
PEAR2_Pyrus_AtomicFileTransaction::createOrOpenPath(), path can't be opened, contents is string
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = PEAR2_Pyrus_AtomicFileTransaction::getTransactionObject(__DIR__ . '/testit/src');

PEAR2_Pyrus_AtomicFileTransaction::begin();

mkdir(__DIR__ . '/testit/.journal-src/foo/bar', 0777, true);
try {
    $atomic->createOrOpenPath('foo', 'hi', 'wb');
    die('should have failed');
} catch (PEAR2_Pyrus_AtomicFileTransaction_Exception $e) {
    $test->assertEquals('Unable to write to foo in ' . __DIR__ .
                        DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR .
                        '.journal-src', $e->getMessage(), 'error msg');
}
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===