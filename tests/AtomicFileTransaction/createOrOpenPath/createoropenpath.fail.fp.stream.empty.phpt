--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::createOrOpenPath(), failure, contents is empty stream
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject(__DIR__ . '/testit/src');

\PEAR2\Pyrus\AtomicFileTransaction::begin();

file_put_contents(__DIR__ . '/testit/blah', 'blah');
$fp = fopen(__DIR__ . '/testit/blah', 'rb');
fread($fp, 55);
try {
    $atomic->createOrOpenPath('foo', $fp, 0664);
    fclose($fp);
    die('should have failed');
} catch (\PEAR2\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Unable to copy to foo in ' . __DIR__ . DIRECTORY_SEPARATOR .
                        'testit' . DIRECTORY_SEPARATOR . '.journal-src', $e->getMessage(), 'error message');
}
fclose($fp);
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===