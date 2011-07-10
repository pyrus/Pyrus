--TEST--
\Pyrus\AtomicFileTransaction::createOrOpenPath(), failure, contents is empty stream
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

file_put_contents(TESTDIR . '/blah', 'blah');
$fp = fopen(TESTDIR . '/blah', 'rb');
fread($fp, 55);
try {
    $instance->createOrOpenPath('foo', $fp, 0664);
    fclose($fp);
    throw new Exception('Expected exception.');
} catch (\RuntimeException $e) {
    $test->assertEquals('Unable to copy to foo in ' . TESTDIR . DIRECTORY_SEPARATOR . '.journal-dir', $e->getMessage(), 'error message');
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