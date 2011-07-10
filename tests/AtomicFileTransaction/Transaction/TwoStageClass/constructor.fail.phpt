--TEST--
\Pyrus\AtomicFileTransaction\Transaction\Base::__construct() invalid arguments
--FILE--
<?php
require __DIR__ . '/../../../test_framework.php.inc';
// Test empty
try {
    new \Pyrus\AtomicFileTransaction\Transaction\Base(null);
    throw new Exception('Exception expected');
} catch (InvalidArgumentException $e) {
    $test->assertEquals('The given path must be a directory.', $e->getMessage(), 'constructor null');
}
// Test !is_string
try {
    new \Pyrus\AtomicFileTransaction\Transaction\Base(1);
    throw new Exception('Exception expected');
} catch (InvalidArgumentException $e) {
    $test->assertEquals('The given path must be a directory.', $e->getMessage(), 'constructor not a string');
}
// Test file_exists($path) && !is_dir($path)
try {
    $path = TESTDIR . DIRECTORY_SEPARATOR . 'file';
    file_put_contents($path, 'content');
    new \Pyrus\AtomicFileTransaction\Transaction\Base($path);
    throw new Exception('Exception expected');
} catch (InvalidArgumentException $e) {
    $test->assertEquals('The given path must be a directory.', $e->getMessage(), 'constructor is file');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===